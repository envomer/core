package model

import (
	"time"
)

type AppInfo struct {
	Version      string
	ConfigExists bool
}

// Err define errors using this struct
type Err struct {
	code    string
	message string
}

// AppInstance is set during runtime and contains
// information such as start time and whether config file exists
type AppInstance struct {
	StartTime    time.Time `json:"startTime"`
	ConfigExists bool
	PID          int64  `json:"pid"`
	Version      string `json:"version"`
}

// AppRuntime stores timers, etc...
type AppRuntime struct {
	SocketTimer *time.Ticker
}

func (a *AppInstance) uptime() time.Duration {
	return time.Since(a.StartTime)
}
