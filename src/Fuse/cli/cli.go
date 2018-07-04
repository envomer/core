package cli

import (
	"fmt"
	"io"
	"os"
	"os/exec"

	"envo.me/Fuse/app"
	"serverbeats.com/beats/logging"

	"github.com/kr/pty"
	"github.com/urfave/cli"
)

func Init() {
	// auth.IsAuthorized(c) // TODO: enable
	// app.Start(true)
	// litter.Dump(c.Command)

	phpPath := "/usr/local/bin/php"
	envoPath := "/Users/anx/Code/appic/envo"
	cmd := exec.Command(phpPath, envoPath)

	// cmd.Stderr = os.Stderr
	// cmd.Stdout = os.Stdout
	// cmd.Stdin = os.Stdin

	// out, err := cmd.Output()
	// if err != nil {
	// 	log.Fatal(err)
	// }
	// log.Println(string(out))

	// out, err := cmd.CombinedOutput()
	// if err != nil {
	// 	log.Fatalf("cmd.Run() failed with %s\n", err)
	// }
	// fmt.Printf("combined out:\n%s\n", string(out))

	// cmd := exec.Command("grep", "--color=auto", "bar")
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

func InitCli() {
	cliApp := cli.NewApp()
	cliApp.Name = "Fuse"
	cliApp.Description = "Ignite the fire within your app"
	cliApp.Usage = ""
	cliApp.Version = app.Info.Version
	cliApp.HideHelp = true

	add(cliApp) // add other commands
	overrideLayout()

	cliApp.Run(os.Args)
}

func perform(c *cli.Context) error {
	// auth.IsAuthorized(c) // TODO: enable
	// app.Start(true)
	// litter.Dump(c.Command)

	phpPath := "/usr/local/bin/php"
	envoPath := "/Users/anx/Code/appic/envo"
	cmd := exec.Command(phpPath, envoPath)

	// cmd.Stderr = os.Stderr
	// cmd.Stdout = os.Stdout
	// cmd.Stdin = os.Stdin

	// out, err := cmd.Output()
	// if err != nil {
	// 	log.Fatal(err)
	// }
	// log.Println(string(out))

	// out, err := cmd.CombinedOutput()
	// if err != nil {
	// 	log.Fatalf("cmd.Run() failed with %s\n", err)
	// }
	// fmt.Printf("combined out:\n%s\n", string(out))

	// cmd := exec.Command("grep", "--color=auto", "bar")
	f, err := pty.Start(cmd)
	if err != nil {
		panic(err)
	}

	// go func() {
	// 	f.Write([]byte("foo\n"))
	// 	f.Write([]byte("bar\n"))
	// 	f.Write([]byte("baz\n"))
	// 	f.Write([]byte{4}) // EOT
	// }()
	io.Copy(os.Stdout, f)

	return nil
}

func checkLsExists() {
	path, err := exec.LookPath("ls")
	if err != nil {
		fmt.Printf("didn't find 'ls' executable\n")
	} else {
		fmt.Printf("'ls' executable is in '%s'\n", path)
	}
}

// Override cli layout
func overrideLayout() {
	cli.AppHelpTemplate = fmt.Sprintf(`{{.Name}} %s
{{.Description}}
	
%s:
  {{.HelpName}} {{if .VisibleFlags}}[global options]{{end}}{{if .Commands}} command [command options]{{end}} {{if .ArgsUsage}}{{.ArgsUsage}}{{else}}[arguments...]{{end}}
  {{if len .Authors}}
%s:
  {{range .Authors}}{{ . }}{{end}}
  {{end}}{{if .VisibleCommands}}
%s:{{range .VisibleCategories}}{{if .Name}}
 %s:{{end}}{{range .VisibleCommands}}
  %s{{"\t"}}{{.Usage}}{{end}}{{end}}{{end}}{{if .VisibleFlags}}
	 
%s:
  {{range .VisibleFlags}}{{.}}
  {{end}}{{end}}{{if .Copyright }}
%s:
  {{.Copyright}}
  {{end}}
`,
		logging.Green("{{.Version}}"),
		logging.Yellow("Usage"),
		logging.Yellow("Authors"),
		logging.Yellow("Available commands"),
		logging.Yellow("{{.Name}}"),
		logging.Green("{{join .Names \", \"}}"),
		logging.Yellow("Options"),
		logging.Yellow("Copyright"))
}

// Add commands to cli
func add(cliApp *cli.App) {
	cliApp.Commands = []cli.Command{
		{
			Name: "backup",
			// Aliases:  []string{"s"},
			// Category: "app",
			Usage: "Backup database",
			Flags: []cli.Flag{
				cli.BoolFlag{Name: "verbose"},
				cli.StringFlag{Name: "token"},
			},
			Action: perform,
		},
	}

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name: "down",
		// Category: "app",
		// Aliases: []string{"t"},
		Usage:  "Put the application into maintenance mode",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name: "up",
		// Category: "app",
		// Aliases: []string{"t"},
		Usage:  "Bring the application out of maintenance mode",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name: "migrate",
		// Category: "app",
		// Aliases: []string{"t"},
		Usage:  "Run the database migrations",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "config:cache",
		Category: "config",
		// Aliases: []string{"t"},
		Usage:  "Cache app config",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "config:clear",
		Category: "config",
		// Aliases: []string{"t"},
		Usage:  "Clear cached configuration",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "config:json",
		Category: "config",
		// Aliases: []string{"t"},
		Usage:  "Return config as JSON",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "make:migration",
		Category: "make",
		// Aliases: []string{"t"},
		Usage:  "Generate a migration",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "make:api",
		Category: "make",
		// Aliases: []string{"t"},
		Usage:  "Generate an API endpoint (API class, model, events, DTO)",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "make:model",
		Category: "make",
		// Aliases: []string{"t"},
		Usage:  "Generate a model",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "migrate",
		Category: "migrate",
		// Aliases: []string{"t"},
		Usage:  "Run the database migrations",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "migrate:rollback",
		Category: "migrate",
		// Aliases: []string{"t"},
		Usage:  "Rollback last database migration",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "migrate:scaffold",
		Category: "migrate",
		// Aliases: []string{"t"},
		Usage:  "Scaffold database migrations (user, teams, ...)",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "migrate:status",
		Category: "migrate",
		// Aliases: []string{"t"},
		Usage:  "See the status of your migrations",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "route:cache",
		Category: "route",
		// Aliases: []string{"t"},
		Usage:  "Cache app routes",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "route:clear",
		Category: "route",
		// Aliases: []string{"t"},
		Usage:  "Clear cached routes",
		Action: perform,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "storate:clear",
		Category: "storate",
		// Aliases: []string{"t"},
		Usage:  "Clear storage folder data",
		Action: perform,
	})
}
