package main

import (
	"fmt"
	"io"
	"os"
	"path/filepath"

	"poll/pollblockchain/health"
	"poll/pollblockchain/manage"

	"github.com/golang/protobuf/jsonpb"
	"github.com/golang/protobuf/proto"
	"go.uber.org/zap"
)

var (
	stopServer chan struct{}

	hubBlockchain *Hub
	arr           []int64

	eventCh                chan struct{}
	blockChainCh           chan bool
	stateServiceBlockchain health.ServingStatus = health.ServingStatus_UNKNOWN
)

func main() {
	action := parseFlag()
	if action == "" {
		return
	}

	prepareLogZap("")
	defer logger.Sync()
	logger.Info("----------------------------------")
	logger.Info("Запуск сервиса для работы с блокчейном")
	logger.Info("----------------------------------")

	//connToEthereum()
	//return

	checkKeys()

	unm = newJsonPBUnmarshaler()

	//opts = prepareConnectGRPC()
	eventCh = make(chan struct{})
	restoreCh = make(chan struct{})
	blockChainCh = make(chan bool)
	go openLocalDB()
	<-eventCh
	localCh = make(chan []byte)
	go putLocalDB(localCh)

	hubBlockchain = newHub()
	go hubBlockchain.run()

	hubGRPC = newHubGRPC()
	go hubGRPC.run()

	checkBlockchain()

	go startConnectGRPC("")
	//arr = []int64{16, 31, 32, 33}

	//tryJson()
	printConfig()

	/*bc := createBlockChain(16, nil)
	bc.checkBlockChain()*/

	/*for _, v := range arr {
		openReadBlockChain(v)
	}*/

	//connGRPC()

	/*for i := 0; i < 10; i++ {
		wg.Add(1)
		name := strconv.Itoa(i) + ".db"
		go openDB(name)
	}
	wg.Wait()*/

	/*bc := NewBlockChain(16, 0, nil)
	defer bc.db.Close()

	cli := CLI{bc}
	cli.Run()*/
	select {}
	//<-stopServer
}

func tryJson() []byte {
	var jsonText string
	var n int

	file, err := os.Open(filepath.Join(config.Blockchain.Path, "json.txt"))
	if err != nil {
		logger.Error("Ошибка открытия файла", zap.Error(err))
		return nil
	}
	defer file.Close()

	data := make([]byte, 1000)

	for {
		n, err = file.Read(data)
		if err == io.EOF {
			break
		}
		jsonText = string(data[:n])
		return data[:n]
	}
	logger.Info("jsonText", zap.String("txt", jsonText))

	conv := &manage.ANSWER{}
	err = jsonpb.UnmarshalString(jsonText, conv)
	if err != nil {
		logger.Error("Ошибка", zap.Error(err))
	} else {
		logger.Info("Protobuf успешно")
	}

	bt, err := proto.Marshal(conv)
	if err != nil {
		logger.Error("Ошибка marshal", zap.Error(err))
	}
	fmt.Println(bt)
	return bt

	convNew := &manage.ANSWER{}
	err = proto.Unmarshal(bt, convNew)
	if err != nil {
		logger.Error("Ошибка unmarshal", zap.Error(err))
	}

	//bb := make([]byte, 8)
	//var yy []*manage.PAYLOAD
	msgAnswer := convNew.GetAnswers()
	logger.Info("UID", zap.String("uid", convNew.Uid))
	for _, answer := range msgAnswer {
		logger.Info("Записи",
			zap.Int64("question_id", answer.QuestionId),
			zap.String("answer_text", answer.AnswerText),
			zap.Int32s("variant_items", answer.VariantItems))
	}

	return nil
}

/*func updateDB() {
	//	t := &Test{"Test", "Value"}

	_ = db.Update(func(tx *bolt.Tx) error {
		bk, err := tx.CreateBucketIfNotExists([]byte("main"))
		if err != nil {
			logger.Error("Ошибка создания корзины", zap.Error(err))
			return nil
		}

		if err := bk.Put([]byte("1"), b.Serialize()); err != nil {
			logger.Error("Ошибка записи в корзину", zap.Error(err))
			return nil
		}

		bks, err := tx.CreateBucketIfNotExists([]byte("second"))
		if err != nil {
			logger.Error("Ошибка создания корзины", zap.Error(err))
			return nil
		}

		if err := bks.Put([]byte("3"), []byte("4")); err != nil {
			logger.Error("Ошибка записи в корзину", zap.Error(err))
			return nil
		}

		return nil
	})
}

func readDB() {
	_ = db.View(func(tx *bolt.Tx) error {
		bk := tx.Bucket([]byte("main"))
		if bk == nil {
			logger.Error("Ошибка открытия корзины", zap.Error(err))
			return nil
		}

		c := bk.Cursor()
		for k, v := c.First(); k != nil; k, v = c.Next() {
			b := DeSerialize(v)
			timestamp := time.Unix(0, b.Timestamp)
			logger.Info("Строки",
				zap.Time("Timestamp", timestamp),
				zap.String("Hash", string(b.Hash)))
		}

		return nil
	})
}*/
