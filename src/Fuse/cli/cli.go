package cli

import (
	"fmt"
	"io"
	"os"
	"os/exec"

	"github.com/kr/pty"
)

func Init() {
	argsWithoutProg := os.Args[1:]

	if len(argsWithoutProg) > 0 {
		if argsWithoutProg[0] == "queue:work" {
			startQueue()
			return
		}
	}

	os.Setenv("FUSE_CLI", "true")

	phpPath := "php"
	envoPath := "envo"
	data := append([]string{envoPath}, argsWithoutProg...)
	cmd := exec.Command(phpPath, data...)

	f, err := pty.Start(cmd)
	if err != nil {
		panic(err)
	}
	// Make sure to close the pty at the end.
	defer func() { _ = f.Close() }() // Best effort.

	// go func() {
	// 	f.Write([]byte("foo\n"))
	// 	f.Write([]byte("bar\n"))
	// 	f.Write([]byte("baz\n"))
	// 	f.Write([]byte{4}) // EOT
	// }()
	io.Copy(os.Stdout, f)
}

func checkLsExists() {
	path, err := exec.LookPath("ls")
	if err != nil {
		fmt.Printf("didn't find 'ls' executable\n")
	} else {
		fmt.Printf("'ls' executable is in '%s'\n", path)
	}
}

func startQueue() {
	fmt.Println("Coming soon. Be patient")
}

func serve() {
	// handle requests. with status report. store data (where?)
	// handle queue. how?
	// socket endpoint.. what for?
}
