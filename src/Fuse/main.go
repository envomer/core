package main

import (
	"fmt"
	"io/ioutil"
	"os"
	"runtime/debug"
	"time"

	"envo.me/Fuse/app"
	"envo.me/Fuse/cli"
	"serverbeats.com/beats/logging"
)

// Init initializes the whole app
func main() {
	defer func() {
		if e := recover(); e != nil {
			now := time.Now().String()
			trace := fmt.Sprintf("DATE   : %s\nMESSAGE: %s\n\n%s", now, e, debug.Stack()) // line 20
			ioutil.WriteFile("trace.beats.log", []byte(trace), 0644)
			fmt.Println()
			fmt.Println()
			fmt.Println(logging.Red("ERROR :"), "exiting app (see trace.beats.txt file)...")
			fmt.Println(logging.Red("REASON:"), e)
			fmt.Println()
			fmt.Println()
			fmt.Println(logging.Green("STACKTRACE:"))
			fmt.Println(trace)
			os.Exit(0)
		}
	}()

	app.Init()
	// config.Init()
	cli.Init()
}
