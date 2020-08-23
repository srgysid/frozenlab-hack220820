package main

import (
	"encoding/binary"
	"errors"
	"path/filepath"
	"strconv"
	"sync"
	"time"

	"poll/pollblockchain/health"
	"poll/pollblockchain/manage"

	"github.com/boltdb/bolt"
	"github.com/golang/protobuf/jsonpb"
	"github.com/golang/protobuf/proto"
	"go.uber.org/zap"
)

var (
	errMainBucketNotFound        = errors.New("Bucket Main in Blockchain file not found")
	errProblemWithBlockchainFile = errors.New("Something problem with Blockchain file")
)

type BlockChain struct {
	mu sync.RWMutex

	// ИД опроса
	idPoll int64

	// Состояние блокчейна
	state string

	// Хэш последнего блока
	tip []byte

	// Генезис
	gen []byte

	// Подключение к файлу БД
	db *bolt.DB

	// Текущие результаты опроса
	resultsNum map[int64][]*manage.COUNTS_VARIANT

	// Текущий индекс для полей со свободным ответом
	resultsText map[int64]*manage.SummaryAnswersTexts

	// Предельное количество голосов
	limitvote int32

	// Текущее количество голосов
	vote int32

	// Таймер для отсчёта таймаута
	timeOutTimer *time.Timer

	// Закрытие файла блокчейна
	close chan struct{}

	// Прочитать корзину результатов
	readBucketResults chan struct{}

	// Записать результаты в корзину результатов
	writeBucketResults chan struct{}

	// Пересчитать результаты
	recountResults chan struct{}

	// Проверить блокчейн
	check chan struct{}

	// Добавить блок
	addBlock chan []byte

	// Действие с блокчейном
	action chan []byte

	// Запрос на получение суммарных результатов блокчейна
	querySummaryResults chan []byte

	// Запрос на получение детальных результатов блокчейна
	queryDetailResults chan []byte

	// Канал для перезагрузки таймера блокчейна
	reloadTimer chan struct{}
}

type BlockChainIterator struct {
	currentHash []byte
	db          *bolt.DB
}

func newBlockChain(db *bolt.DB, idPoll int64, tip, gen []byte, limitVote int32, state string) *BlockChain {
	return &BlockChain{
		idPoll:              idPoll,
		tip:                 tip,
		gen:                 gen,
		db:                  db,
		resultsNum:          make(map[int64][]*manage.COUNTS_VARIANT),
		resultsText:         make(map[int64]*manage.SummaryAnswersTexts),
		limitvote:           limitVote,
		vote:                0,
		state:               state,
		timeOutTimer:        time.NewTimer(config.Blockchain.Timeout),
		close:               make(chan struct{}),
		readBucketResults:   make(chan struct{}),
		writeBucketResults:  make(chan struct{}),
		recountResults:      make(chan struct{}),
		check:               make(chan struct{}),
		addBlock:            make(chan []byte),
		action:              make(chan []byte),
		querySummaryResults: make(chan []byte),
		queryDetailResults:  make(chan []byte),
		reloadTimer:         make(chan struct{}),
	}
}

func (bc *BlockChain) startPoll() {

	bc.timeOutTimer.Stop()

	if bc.state == "close" {
		bc.timeOutTimer = time.NewTimer(config.Blockchain.Timeout)

		defer func() {
			bc.timeOutTimer.Stop()
		}()
	}

	for {
		select {
		case <-bc.readBucketResults:
			logger.Info("Считываем результаты из корзины результатов", zap.Int64("idPoll", bc.idPoll))
			bc.readResultFromBucket()

		case <-bc.writeBucketResults:
			logger.Info("Выполняется запись результатов в корзину result", zap.Int64("idPoll", bc.idPoll))
			bc.writeResultToBucket()

		case <-bc.recountResults:
			logger.Info("Выполняется перерасчёт результатов", zap.Int64("idPoll", bc.idPoll))
			bc.recountResultsFromBucket()

		case <-bc.check:
			logger.Info("Выполняется проверка целостности блокчейна", zap.Int64("idPoll", bc.idPoll))
			bc.checkBlockChain()

		case <-bc.close:
			hubBlockchain.unregister <- bc.idPoll
			bc.db.Close()
			logger.Info("Закрыли базу", zap.Int64("db", bc.idPoll))
			return

		case data := <-bc.addBlock:
			logger.Debug("Добавляем блок")
			go bc.checkBeforeAddBlock(data)

		case params := <-bc.querySummaryResults:
			go bc.sendSummaryResults(params)

		case params := <-bc.queryDetailResults:
			go bc.sendDetailResults(params)

		case <-bc.timeOutTimer.C:
			logger.Debug("Таймаут ожидания активности для опроса завершён",
				zap.Int64("idPoll", bc.idPoll),
				zap.String("state", bc.state))
			hubBlockchain.unregister <- bc.idPoll
			bc.db.Close()
			logger.Info("Закрыли базу по таймайту", zap.Int64("db", bc.idPoll))
			return

		case <-bc.reloadTimer:
			if bc.state == "close" {
				bc.timeOutTimer.Reset(config.Blockchain.Timeout)
				logger.Debug("Таймер ожидания активности для опроса перезагружен",
					zap.Int64("idPoll", bc.idPoll),
					zap.String("state", bc.state))
			}

		}
	}
}

// Функция создаёт новый блокчейн с генезис-блоком
func createBlockChain(idPoll int64, data []byte) *BlockChain {
	var tip []byte

	conv := &manage.Items{}
	err := jsonpb.UnmarshalString(string(data), conv)
	if err != nil {
		logger.Error("Ошибка", zap.Error(err))
	} else {
		logger.Info("Protobuf успешно")
	}

	// Перед попыткой создания БД блокчейна проверим наличие БД на файловой системе
	// Тем самым защитимся от некорректных записей со стороны хранилища транзакций
	path, _ := getFileNameBlockchain(idPoll)
	if path != "" {
		blockChainCh <- true
		return nil
	}

	pathDb := filepath.Join(getPathFileOnServer(idPoll), strconv.FormatInt(idPoll, 10)+".db")
	db, err := bolt.Open(pathDb, 0600, &bolt.Options{Timeout: 1 * time.Second})
	if err != nil {
		logger.Panic("Ошибка открытия БД",
			zap.String("path", pathDb),
			zap.Error(err))
	}

	err = db.Update(func(tx *bolt.Tx) error {
		b1 := tx.Bucket([]byte("main"))
		if b1 == nil {
			logger.Info("Блокчейн в базе не обнаружен. Создаётся новый")
			genesis := NewBlock(nil, nil)

			b1, err := tx.CreateBucket([]byte("main"))
			if err != nil {
				logger.Panic("Ошибка создания корзины", zap.Error(err))
			}

			err = b1.Put(genesis.Hash, SerializeBlock(genesis))
			if err != nil {
				logger.Panic("Ошибка вставки записи", zap.Error(err))
			}

			err = b1.Put([]byte("l"), genesis.Hash)
			if err != nil {
				logger.Panic("Ошибка вставки записи обозначающей текущий последний блок", zap.Error(err))
			}
			tip = genesis.Hash

			err = b1.Put([]byte("g"), genesis.Hash)
			if err != nil {
				logger.Panic("Ошибка вставки записи обозначающей хэш генезиса", zap.Error(err))
			}

			bb64 := make([]byte, 8)
			binary.LittleEndian.PutUint64(bb64, uint64(conv.PollId))
			err = b1.Put([]byte("p"), bb64)
			if err != nil {
				logger.Panic("Ошибка вставки записи ИД опроса", zap.Error(err))
			}

			bb32 := make([]byte, 4)
			binary.LittleEndian.PutUint32(bb32, uint32(conv.Limitvote))
			err = b1.Put([]byte("lv"), bb32)
			if err != nil {
				logger.Panic("Ошибка вставки записи предела количества голосов", zap.Error(err))
			}

			err = b1.Put([]byte("s"), []byte("open"))
			if err != nil {
				logger.Panic("Ошибка вставки записи статуса блокчейна", zap.Error(err))
			}
		} else {
			tip = b1.Get([]byte("l"))
		}

		b2 := tx.Bucket([]byte("result"))
		if b2 == nil {
			logger.Info("Создаём корзину результатов", zap.Int64("PollId", conv.PollId))
			b2, err := tx.CreateBucket([]byte("result"))
			if err != nil {
				logger.Panic("Ошибка создания корзины",
					zap.Int64("ИД опроса", conv.PollId),
					zap.Error(err))
			}

			// Распарсим пришедший JSON в нашу структуру
			/*conv := &manage.Items{}
			err = jsonpb.UnmarshalString(string(data), conv)
			if err != nil {
				logger.Error("Ошибка", zap.Error(err))
			} else {
				logger.Info("Protobuf успешно")
			}*/

			// В корзине результатов данных хранятся в следующем виде
			// "id_вопроса" -> []{вариант ответа, количество, тип ввода}
			bb := make([]byte, 8)
			var yy []*manage.COUNTS_VARIANT

			msgItems := conv.GetItems()
			for _, item := range msgItems {
				msgAnswers := item.GetAnswersId()
				// Исключим все вопросы, у которых ответ только текст
				if len(msgAnswers) > 0 {
					// Преобразуем int64 в []byte
					binary.LittleEndian.PutUint64(bb, uint64(item.QuestionId))

					for _, answer := range msgAnswers {
						sess := &manage.COUNTS_VARIANT{
							Id:    answer.Id,
							Count: 0, //answer.Count,
							Free:  answer.Free,
						}
						yy = append(yy, sess)
					}

					tt := &manage.SummaryAnswersCounts{}
					tt.AnswersId = yy

					// Преобразуем нашу структуру protobuf в массив байт (grpc)
					arr, err := proto.Marshal(tt)
					if err != nil {
						logger.Error("Error", zap.Error(err))
					}

					err = b2.Put(bb, arr)
					if err != nil {
						logger.Panic("Ошибка вставки записи", zap.Error(err))
					}

					yy = yy[:0]
				}
			}
		}

		b3 := tx.Bucket([]byte("text"))
		if b3 == nil {
			logger.Info("Создаём корзину с индексом для текстовых полей", zap.Int64("ИД опроса", conv.PollId))
			b3, err := tx.CreateBucket([]byte("text"))
			if err != nil {
				logger.Panic("Ошибка создания корзины",
					zap.Int64("ИД опроса", conv.PollId),
					zap.Error(err))
			}

			bb := make([]byte, 8)
			var yy []*manage.TEXTS_VARIANT
			msgItems := conv.GetItems()
			for _, item := range msgItems {
				// Преобразуем int64 в []byte
				binary.LittleEndian.PutUint64(bb, uint64(item.QuestionId))

				msgAnswers := item.GetAnswersId()

				// Пройдёмся по вопросам в поиске поля со свободным вводом
				if len(msgAnswers) > 0 {
					for _, answer := range msgAnswers {
						if answer.Free == true {
							yy = append(yy, &manage.TEXTS_VARIANT{
								Id:   answer.Id,
								Hash: nil,
							})
						}
					}
				}

				// Дополнительно включим вопросы, которые целиком относятся к текстовым полям
				if len(yy) > 0 || len(msgAnswers) == 0 {
					tt := &manage.SummaryAnswersTexts{}
					tt.AnswerstextHash = nil
					tt.AnswersId = yy

					// Преобразуем нашу структуру protobuf в массив байт (grpc)
					arr, err := proto.Marshal(tt)
					if err != nil {
						logger.Error("Error", zap.Error(err))
					}

					err = b3.Put(bb, arr)
					if err != nil {
						logger.Panic("Ошибка вставки записи", zap.Error(err))
					}

					yy = yy[:0]
				}
			}
		}

		return nil
	})

	if err != nil {
		logger.Panic("Ошибка вставки записи", zap.Error(err))
	}

	bc := newBlockChain(db, idPoll, tip, tip, conv.Limitvote, "")
	hubBlockchain.register <- bc
	blockChainCh <- true

	return bc
}

// Функция выполняет проверку, можно ли добавлять новый блок в блокчейн
func (bc *BlockChain) checkBeforeAddBlock(data []byte) {
	// Если блокчейн открыт для записи
	if bc.state == "open" {
		// Добавляем новый блок
		bc.AddBlock(data)
		// И проверяем есть ли для блокчейна предел голосов.
		// Если есть и предел достигнут, то закрываем блокчейн для записи (установка статуса) и запускаем таймер на выгрузку из памяти блокчейна
		if bc.checkLimitVote() {
			bc.Close()
			bc.reloadTimer <- struct{}{}
		}
		// Сообщаем, что блокчейн ещё работает
		blockChainCh <- true
		return
	}

	blockChainCh <- false
}

// Функция добавляет в блокчейн новый блок
func (bc *BlockChain) AddBlock(data []byte) {
	var lastHash []byte

	err := bc.db.View(func(tx *bolt.Tx) error {
		b := tx.Bucket([]byte("main"))
		lastHash = b.Get([]byte("l"))
		//logger.Debug("lastHash", zap.String("lastHash", string(lastHash)))

		return nil
	})

	if err != nil {
		logger.Panic("Ошибка просмотра корзины", zap.Error(err))
	}

	newBlock := NewBlock(data, lastHash)

	err = bc.db.Update(func(tx *bolt.Tx) error {
		b := tx.Bucket([]byte("main"))

		err := b.Put(newBlock.Hash, SerializeBlock(newBlock))
		if err != nil {
			logger.Panic("Ошибка вставки новой записи", zap.Error(err))
		} else {
			logger.Debug("Добавили")
		}

		err = b.Put([]byte("l"), newBlock.Hash)
		if err != nil {
			logger.Panic("Ошибка вставки новой записи", zap.Error(err))
		}

		bc.tip = newBlock.Hash
		bc.vote = bc.vote + 1

		return nil
	})

	bc.CompareBuckets(newBlock.Data)
}

func (bc *BlockChain) Iterator() *BlockChainIterator {
	bci := &BlockChainIterator{bc.tip, bc.db}

	return bci
}

func (i *BlockChainIterator) Next() *manage.Block {
	var block *manage.Block

	err := i.db.View(func(tx *bolt.Tx) error {
		b := tx.Bucket([]byte("main"))
		encodedBlock := b.Get(i.currentHash)
		block = DeSerializeBlock(encodedBlock)

		return nil
	})

	if err != nil {
		logger.Panic("Ошибка просмотра корзины", zap.Error(err))
	}

	i.currentHash = block.Prevblockhash

	return block
}

// Функция проверяет состояние блокчейна
func (bc *BlockChain) checkBlockChain() {
	currentHash := bc.tip
	countBlocks := 0

	err := bc.db.View(func(tx *bolt.Tx) error {
		b := tx.Bucket([]byte("main"))

		if b == nil {
			logger.Info("Корзина не обнаружена")
			return nil
		}

		for currentHash != nil {
			block := DeSerializeBlock(b.Get(currentHash))

			if block.Prevblockhash != nil {
				countBlocks = countBlocks + 1
			}

			currentHash = block.Prevblockhash
		}
		logger.Info("Количество записей в блокчейне", zap.Int("Найдено", countBlocks))

		return nil
	})

	if err != nil {
		logger.Error("Ошибка открытия файла на чтение", zap.Error(err))
	}
}

// Функция считывает количество записей в корзине
func (bc *BlockChain) restoreLimitvote() {
	err := bc.db.View(func(tx *bolt.Tx) error {
		b := tx.Bucket([]byte("main"))

		if b == nil {
			logger.Info("Корзина не обнаружена")
			return nil
		}

		b.ForEach(func(k, v []byte) error {
			bc.vote = bc.vote + 1

			return nil
		})
		return nil
	})

	if err != nil {
		logger.Panic("Ошибка чтения корзины", zap.Error(err))
	}
}

// Функция подсчитывает количество голосов, находящихся в блокчейне
func (bc *BlockChain) getCurrentVote(b *bolt.Bucket) {
	bc.vote = -1 // отнимаем 1, так как существует генезис-блок

	b.ForEach(func(k, v []byte) error {
		switch string(k) {
		case "l", "lv", "s", "p", "g":
			break

		default:
			bc.vote = bc.vote + 1
		}

		return nil
	})
}

// Функция переводит статус блокчейна в состояние "Закрыто"
func (bc *BlockChain) Close() {
	err := bc.db.Update(func(tx *bolt.Tx) error {
		b1 := tx.Bucket([]byte("main"))
		if b1 != nil {
			err := b1.Put([]byte("s"), []byte("close"))
			if err != nil {
				logger.Panic("Ошибка обновления записи статуса блокчейна", zap.Error(err))
			}
		}
		return nil
	})

	if err != nil {
		logger.Panic("Ошибка обновления корзины", zap.Error(err))
		return
	}

	bc.state = "close"
	hubGRPC.data <- makeResponseToServer(0, makeExtraMessageBytes("finish", bc.idPoll, ""))
	logger.Info("Изменён статус блокчейна",
		zap.Int64("Poll", bc.idPoll),
		zap.String("Status", bc.state))
}

// Функция для принудительного открытия блокчейна с ФС
func openReadBlockChain(idPoll int64) error {
	var tip, gen []byte
	var limitVote int32
	var state string

	path, err := getFileNameBlockchain(idPoll)
	if err != nil {
		logger.Info("Блокчейн не существует", zap.Error(err))
		hubGRPC.data <- makeResponseAPIToServer(0, manage.TypeQuery_UNKNOWN, nil, 0)
		return err
	} else {
		logger.Info("Блокчейн найден", zap.String("path", path))
	}

	db, err := bolt.Open(path, 0600, nil)
	if err != nil {
		logger.Info("Ошибка открытия БД", zap.Error(err))
		return err
	}

	err = db.View(func(tx *bolt.Tx) error {
		b1 := tx.Bucket([]byte("main"))
		if b1 != nil {
			gen = b1.Get([]byte("g"))
			tip = b1.Get([]byte("l"))
			state = string(b1.Get([]byte("s")))
			limitVote = int32(binary.LittleEndian.Uint32(b1.Get([]byte("lv"))))
		} else {
			logger.Info("Отсутствует корзина main", zap.Int64("db", idPoll))
		}

		b1 = tx.Bucket([]byte("result"))
		if b1 == nil {
			logger.Info("Отсутствует корзина result", zap.Int64("db", idPoll))
		}

		return nil
	})

	if err != nil {
		logger.Info("Ошибка просмотра БД")
	}

	if state == "close" {
		bc := newBlockChain(db, idPoll, tip, gen, limitVote, state)
		hubBlockchain.register <- bc
		bc.check <- struct{}{}
		bc.readBucketResults <- struct{}{}
	} else {
		logger.Warn("Открываемый блокчейн не находится в состоянии финиш",
			zap.Int64("blockchain", idPoll),
			zap.String("state", state))
	}

	return nil
}

// Функция проверяет достиг ли опрос предельного количества голосов
func (bc *BlockChain) checkLimitVote() bool {
	if bc.limitvote > 0 {
		if bc.vote == bc.limitvote {
			return true
		}
	}

	return false
}

// Функция проверки БД блокчейна при запуске
func checkBlockchain() {
	logger.Info("Начало процедуры проверки БД блокчейнов")
	stateServiceBlockchain = health.ServingStatus_RESTORE

	arrPathBlockchain, err := getAllFilesFromDir(config.Blockchain.Path, ".db")
	if err != nil {
		logger.Fatal("Ошибка проверки директории с файлами блокчейна",
			zap.String("Директория", config.Blockchain.Path),
			zap.String("Расширение", ".db"),
			zap.Error(err))
	}

	for _, name := range arrPathBlockchain {
		db, err := bolt.Open(string(name), 0600, nil)
		if err != nil {
			logger.Fatal("Ошибка открытия файла блокчейна",
				zap.ByteString("Имя файла", name),
				zap.Error(err))
		}

		bc := newBlockChain(db, 0, nil, nil, 0, "")
		err = bc.fillFromFile()
		if err != nil {
			logger.Fatal("Ошибка чтения данных блокчейна",
				zap.ByteString("Имя файла", name),
				zap.Error(err))
		}
		hubBlockchain.register <- bc
		bc.recountResultsFromBucket()
		bc.rebuildIndexTextFromBucket()

		err = bc.db.View(func(tx *bolt.Tx) error {
			b1 := tx.Bucket([]byte("main"))
			if b1 != nil {
				//b1.Put([]byte("s"), []byte("open")) // Для отладки (заменить View на Update)
				logger.Debug("Статус блокчейна",
					zap.Int64("idPoll", bc.idPoll),
					zap.ByteString("name", name),
					zap.String("state", bc.state))

				switch bc.state {
				case "close": // Если блокчейн находится в состоянии закрыт, то не делаем ничего
					bc.close <- struct{}{}
					return nil

				case "open":
					logger.Debug("limitVote",
						zap.Int64("idPoll", bc.idPoll),
						zap.ByteString("name", name),
						zap.Int32("lv", bc.limitvote))

					// Если у опроса установлен предел голосов, то проверим блокчейн на соответствие этому условию
					// При несоответствии закроем
					if bc.limitvote > 0 {
						bc.getCurrentVote(b1)
						logger.Debug("currentVote",
							zap.Int64("idPoll", bc.idPoll),
							zap.ByteString("name", name),
							zap.Int32("currentVote", bc.vote))

						if bc.checkLimitVote() {
							bc.Close()
							bc.close <- struct{}{}
							return nil
						}
					}
				}
			} else {
				logger.Info("Отсутствует корзина main", zap.ByteString("Имя файла", name))
			}
			bc.checkBlockChain()
			return nil
		})
	}
	stateServiceBlockchain = health.ServingStatus_SERVING
}

// Функция заполняет параметры текущего блокчейна из записей файла БД блокчейна
func (bc *BlockChain) fillFromFile() error {
	err := bc.db.View(func(tx *bolt.Tx) error {
		b := tx.Bucket([]byte("main"))

		if b != nil {
			bc.idPoll = int64(binary.LittleEndian.Uint64(b.Get([]byte("p"))))
			bc.limitvote = int32(binary.LittleEndian.Uint32(b.Get([]byte("lv"))))
			bc.gen = b.Get([]byte("g"))
			bc.tip = b.Get([]byte("l"))
			bc.state = string(b.Get([]byte("s")))
			bc.readResultFromBucket()
			bc.getCurrentVote(b)
		} else {
			return errMainBucketNotFound
		}
		return nil
	})

	if err != nil {
		logger.Error("Ошибка получения ИД опроса из БД блокчейна", zap.Error(err))
		return errProblemWithBlockchainFile
	}
	return nil
}
