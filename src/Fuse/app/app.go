package app

import (
	"fmt"
	"os"
	"os/signal"
	"strconv"
	"time"

	"envo.me/Fuse/model"
	"serverbeats.com/beats/logging"
)

var Info *model.AppInstance
var Runtime *model.AppRuntime
var logger *logging.Logger

func Init() {
	Info = &model.AppInstance{}
	Info.Version = "v0.0.1"
	Info.StartTime = time.Now()

	Runtime = &model.AppRuntime{}

	logger = logging.GetLogger("app", logging.BGreen)
}

func Kill() {
	logger.Println(logging.Green("Stopping app..."))

	// b, err := ioutil.ReadFile(config.GetInstance().Path + "/cache/serverbeats.pid") // just pass the file name
	// if err != nil {
	// 	fmt.Print(err)
	// } else {
	// 	pid, _ := strconv.ParseInt(string(b), 10, 64)
	// 	syscall.Kill(int(pid), syscall.SIGTERM)
	// }
}

func Start(verbose bool) {
	if verbose == false {
		// logFile := helper.LogToFile()
		// defer logFile.Close()
	}

	Info.PID = int64(os.Getpid())
	pid := strconv.FormatInt(Info.PID, 10)
	logger.Println("Writing pid (" + logging.Green(pid) + ") to file")
	// ioutil.WriteFile(config.GetInstance().Path+"/cache/serverbeats.pid", []byte(pid), 0644)

	// statsAll = &Stats{}
	// var err error
	// appInfo.SystemInfo, err = host.Info()
	// if err != nil {
	// 	log.Fatal(err)
	// }

	// notification.Send("Hi. this is a test from beats")

	// if !db.Start() { // start db before everything else
	// 	return
	// }
	// defer db.Shutdown()

	// if !network.Start() { // start network before everything else
	// 	return
	// }
	// defer network.Shutdown()

	// if !stat.Start() { // start stat before everything else
	// 	return
	// }
	// defer stat.Shutdown()

	// if !logs.Start() {
	// 	return // ??
	// }
	// defer logs.Shutdown()

	// if !disk.Start() {
	// 	return // ??
	// }
	// defer disk.Shutdown()

	// if !notification.Start() {
	// 	return
	// }
	// defer notification.Shutdown()

	// go processWatch()
	// stat.Start()
	// disk.Start()
	// ping.Start()
	// port.Start()
	// memory.Start()
	// mysql.Start()
	// network.Start()

	// // implement pushover plugin or maybe just use server?
	// // - https://github.com/nlopes/slack

	// // COMING SOON

	// go portsCheck("127.0.0.1", []int{80, 135, 139, 443, 445, 3000, 3001})

	// socket.Start()

	listenToCloseSignal()
}

func listenToCloseSignal() {
	// var gracefulStop = make(chan os.Signal)
	// signal.Notify(gracefulStop, syscall.SIGTERM)
	// signal.Notify(gracefulStop, syscall.SIGINT)
	// go func() {
	// 	// sig := <-gracefulStop
	// 	_ = <-gracefulStop
	// 	// fmt.Printf("caught signal: %+v", sig)
	// 	log.Println(logging.Red("\nStopped app"))
	// 	// time.Sleep(1 * time.Second)
	// 	os.Exit(0)
	// }()

	signalChan := make(chan os.Signal, 1)
	signal.Notify(signalChan, os.Interrupt)
	exit := false
	for !exit {
		select {
		case sig := <-signalChan:
			fmt.Println("")
			logger.Printf("Received signal %s.", sig.String())
			exit = true
		}
	}
}
