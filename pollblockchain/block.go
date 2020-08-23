package main

import (
	"bytes"
	"crypto/sha256"
	"encoding/binary"
	"io"
	"strconv"
	"time"

	"poll/pollblockchain/manage"

	"github.com/boltdb/bolt"
	"github.com/golang/protobuf/jsonpb"
	"github.com/golang/protobuf/proto"
	"go.uber.org/zap"
)

var (
	unm *jsonpb.Unmarshaler
)

func NewBlock(data []byte, prevblockhash []byte) *manage.Block {
	conv := &manage.ANSWER{}

	// Проверим является ли блок генезис-блоком
	if data != nil {
		// Получили набор байт, который пришёл из вне
		in := bytes.NewReader(data)

		// Приведём набор байт в соответствие с необходимой нам структурой в protobuf
		err := unm.Unmarshal(in, conv)
		if err != nil {
			if err != io.EOF {
				logger.Error("Ошибка", zap.Error(err))
				return nil
			}
		}
	} else {
		conv.Uid = "New Genesis Block"
	}

	logger.Info("Protobuf успешно",
		zap.Float64("latitude", conv.Latitude),
		zap.Float64("longitude", conv.Longitude),
		zap.String("uid", conv.Uid))

	block := &manage.Block{
		Timestamp:     time.Now().UnixNano(),
		Data:          conv,
		Prevblockhash: prevblockhash,
		Hash:          []byte{}}
	hash := SetHash(block)
	block.Hash = hash

	return block
}

func SerializeBlock(b *manage.Block) []byte {
	bt, err := proto.Marshal(b)
	if err != nil {
		logger.Error("Ошибка marshal", zap.Error(err))
	}
	return bt
}

func DeSerializeBlock(block []byte) *manage.Block {
	convNew := &manage.Block{}
	err := proto.Unmarshal(block, convNew)
	if err != nil {
		logger.Error("Ошибка unmarshal", zap.Error(err))
	}

	return convNew
}

func SetHash(b *manage.Block) []byte {
	timestamp := []byte(strconv.FormatInt(b.Timestamp, 10))
	date, err := proto.Marshal(b.Data)
	if err != nil {
		logger.Error("Ошибка marshal", zap.Error(err))
	}
	headers := bytes.Join([][]byte{b.Prevblockhash, date, timestamp}, []byte{})
	hash := sha256.Sum256(headers)

	return hash[:]
}

// Функция добавляет ответ пользователя в структуру результатов (в памяти)
func (bc *BlockChain) CompareBuckets(conv *manage.ANSWER) {

	// Выберем ответы, которые пришли от пользователя
	msgAnswersPayload := conv.GetAnswers()

	// Пробежимся по вариантам ответов от пользователя
	for _, itemPayload := range msgAnswersPayload {

		// Если в структуре корзины result найден данный ответ
		bc.mu.Lock()
		if data, ok := bc.resultsNum[itemPayload.QuestionId]; ok {

			// Пробежимся по массиву ответов для варианта от пользователя
			for _, j := range itemPayload.VariantItems {

				// Теперь пробежимся по вариантам из корзины и добавим результат
				for _, l := range data {

					if l.Id == int64(j) {
						//logger.Info("Добавили")
						l.Count = l.Count + 1

						if l.Free == true {
							if data, ok := bc.resultsText[itemPayload.QuestionId]; ok {
								for _, v := range data.AnswersId {
									if v.Id == l.Id {
										v.Hash = append(v.Hash, bc.tip[:config.Blockchain.SizeIndexFree])
									}
								}
							}
						}
					}
				}
			}
		} else if data, ok := bc.resultsText[itemPayload.QuestionId]; ok {
			// Вариант когда вопрос является полностью текстовым
			if itemPayload.AnswerText != "" {
				data.AnswerstextHash = append(data.AnswerstextHash, bc.tip[:config.Blockchain.SizeIndexFree])
			} else { // Вариант когда один из вариантов ответа является текстовым
				if len(itemPayload.FreeText) > 0 {
					for _, j := range itemPayload.FreeText {
						if j.AnswerText != "" {
							for _, j1 := range data.AnswersId {
								if int64(j.AnswerId) == j1.Id {
									j1.Hash = append(j1.Hash, bc.tip[:config.Blockchain.SizeIndexFree])
								}
							}
						}
					}
				}
			}
		}
		bc.mu.Unlock()
	}
}

func (bc *BlockChain) readResultFromBucket() {
	// Возьмём текущие результаты из корзины result
	// и засунем их в map
	err := bc.db.View(func(tx *bolt.Tx) error {
		b := tx.Bucket([]byte("result"))

		if b == nil {
			logger.Info("Корзина не обнаружена")
			return nil
		}

		b.ForEach(func(k, v []byte) error {
			// Преобразуем []byte в int64
			k1 := int64(binary.LittleEndian.Uint64(k))

			// Преобразуем набор байт из BoltDB в структуру proto
			v1 := &manage.SummaryAnswersCounts{}
			err := proto.Unmarshal(v, v1)
			if err != nil {
				logger.Error("Ошибка unmarshal", zap.Error(err))
			}

			bc.mu.Lock()
			bc.resultsNum[k1] = v1.GetAnswersId()
			bc.mu.Unlock()

			return nil
		})
		return nil
	})

	if err != nil {
		logger.Panic("Ошибка чтения записи", zap.Error(err))
	}

	err = bc.db.View(func(tx *bolt.Tx) error {
		b := tx.Bucket([]byte("text"))

		if b == nil {
			logger.Info("Корзина не обнаружена")
			return nil
		}

		b.ForEach(func(k, v []byte) error {
			// Преобразуем []byte в int64
			k1 := int64(binary.LittleEndian.Uint64(k))

			// Преобразуем набор байт из BoltDB в структуру proto
			v1 := &manage.SummaryAnswersTexts{}
			err := proto.Unmarshal(v, v1)
			if err != nil {
				logger.Error("Ошибка unmarshall", zap.Error(err))
			}

			bc.mu.Lock()
			bc.resultsText[k1] = &manage.SummaryAnswersTexts{
				AnswerstextHash: v1.GetAnswerstextHash(),
				AnswersId:       v1.GetAnswersId(),
			}
			bc.mu.Unlock()

			return nil
		})
		return nil
	})

	if err != nil {
		logger.Panic("Ошибка чтения записи", zap.Error(err))
	}
}

func (bc *BlockChain) readResult() {
	bc.mu.RLock()
	for k, v := range bc.resultsNum {
		for _, item := range v {

			if item.Count > 0 {
				logger.Info("Record",
					zap.Int64("k", k),
					zap.Int64("ID", item.Id),
					zap.Int32("Count", item.Count))
			}
		}
	}
	bc.mu.RUnlock()
}

func (bc *BlockChain) writeResultToBucket() {
	// Возьмём текущие результаты из map
	// и засунем их в корзину result
	err := bc.db.Update(func(tx *bolt.Tx) error {
		bb := make([]byte, 8)
		b := tx.Bucket([]byte("result"))

		if b == nil {
			logger.Info("Корзина не обнаружена")
			return nil
		}

		bc.mu.RLock()
		for k, v := range bc.resultsNum {
			binary.LittleEndian.PutUint64(bb, uint64(k))

			tt := &manage.SummaryAnswersCounts{}
			tt.AnswersId = v

			arr, err := proto.Marshal(tt)
			if err != nil {
				logger.Error("Error", zap.Error(err))
			}

			err = b.Put(bb, arr)
			if err != nil {
				logger.Panic("Ошибка вставки новой записи", zap.Error(err))
			}
		}
		bc.mu.RUnlock()

		b = tx.Bucket([]byte("text"))
		if b == nil {
			logger.Info("Корзина не обнаружена")
			return nil
		}

		bc.mu.RLock()
		for k, v := range bc.resultsText {
			binary.LittleEndian.PutUint64(bb, uint64(k))

			tt := &manage.SummaryAnswersTexts{}
			tt.AnswerstextHash = v.AnswerstextHash
			tt.AnswersId = v.AnswersId

			arr, err := proto.Marshal(tt)
			if err != nil {
				logger.Error("Error", zap.Error(err))
			}

			err = b.Put(bb, arr)
			if err != nil {
				logger.Panic("Ошибка вставки новой записи", zap.Error(err))
			}
		}
		bc.mu.RUnlock()

		return nil
	})

	if err != nil {
		logger.Panic("Ошибка чтения записи", zap.Error(err))
	}
	logger.Info("Окончена запись")
}

func (bc *BlockChain) rebuildIndexTextFromBucket() {
	currentHash := bc.tip
	logger.Debug("Начинаем перестроение индекса")

	err := bc.db.View(func(tx *bolt.Tx) error {
		b := tx.Bucket([]byte("main"))

		if b == nil {
			logger.Error("Не обнаружена корзина")
			return nil
		}

		for currentHash != nil {
			block := DeSerializeBlock(b.Get(currentHash))
			if block.Prevblockhash != nil {

				for _, itemPayload := range block.Data.Answers {

					if data, ok := bc.resultsText[itemPayload.QuestionId]; ok {

						// Вариант когда вопрос является полностью текстовым
						if itemPayload.AnswerText != "" {
							data.AnswerstextHash = append(data.AnswerstextHash, currentHash[:config.Blockchain.SizeIndexFree])
						} else { // Вариант когда один из вариантов ответа является текстовым
							if len(itemPayload.FreeText) > 0 {
								for _, j := range itemPayload.FreeText {
									if j.AnswerText != "" {
										for _, j1 := range data.AnswersId {
											if j1.Id == int64(j.AnswerId) {
												j1.Hash = append(j1.Hash, currentHash[:config.Blockchain.SizeIndexFree])
											}
										}
									}
								}
							}
						}
					}
				}
			}

			currentHash = block.Prevblockhash
		}

		return nil
	})

	if err != nil {
		logger.Error("Ошибка перестроения индекса", zap.Error(err))
	}

	for _, j := range bc.resultsText {
		if len(j.AnswerstextHash) > 0 {
			j.AnswerstextHash = reverseArray(j.AnswerstextHash)
		} else {
			if len(j.AnswersId) > 0 {
				for _, j1 := range j.AnswersId {
					j1.Hash = reverseArray(j1.Hash)
				}
			}
		}
	}

	/*for i, j := range tempResultsText {
		if j.AnswerstextHash != nil {
			for _, j1 := range j.AnswerstextHash {
				logger.Info("tempResultsText AnswersTextHash", zap.Int64("i", i), zap.String("hash", hex.EncodeToString(j1)))
			}
		} else {
			for i1, j1 := range j.AnswersId {
				for i2, j2 := range j1.Hash {
					logger.Info("tempResultsText AnswerId", zap.Int64("i", i), zap.Int("i1", i1), zap.Int("i2", i2), zap.String("hash", hex.EncodeToString(j2)))
				}
			}
		}
	}*/
}

func (bc *BlockChain) recountResultsFromBucket() {
	var block *manage.Block

	// Надо ли ставить блокировку на момент перерасчёта результатов?
	err := bc.db.View(func(tx *bolt.Tx) error {
		b := tx.Bucket([]byte("main"))

		if b == nil {
			logger.Error("Не обнаружена корзина")
			return nil
		}

		// Проидёмся по каждой записи в корзине ответов.
		b.ForEach(func(k, v []byte) error {
			switch string(k) {
			case "l", "lv", "s", "p", "g":
				return nil

			default:
				block = DeSerializeBlock(v)
			}

			if block.Prevblockhash != nil { // Не является генезис блоком
				msgAnswersPayload := block.Data.GetAnswers()

				for _, itemPayload := range msgAnswersPayload {
					// Если в структуре корзины result найден данный ответ
					if state, ok := bc.resultsNum[itemPayload.QuestionId]; ok {

						// Пробежимся по массиву ответов для варианта от пользователя
						for _, j := range itemPayload.VariantItems {

							// Теперь пробежимся по вариантам из корзины и добавим результат
							for _, l := range state {

								if l.Id == int64(j) {
									//logger.Info("Добавили")
									l.Count = l.Count + 1
								}
							}
						}
					}
				}
			}

			return nil
		})

		return nil
	})

	if err != nil {
		logger.Error("Ошибка открытия файла на чтение", zap.Error(err))
	}
}

func newJsonPBUnmarshaler() *jsonpb.Unmarshaler {
	return &jsonpb.Unmarshaler{
		AllowUnknownFields: true,
	}
}
