package cli

import (
	"fmt"
	"os"

	"envo.me/Fuse/app"
	"serverbeats.com/beats/logging"

	"github.com/urfave/cli"
)

func Init() {
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

func start(c *cli.Context) error {
	// auth.IsAuthorized(c) // TODO: enable
	app.Start(true)

	return nil
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
			Action: start,
		},
	}

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name: "down",
		// Category: "app",
		// Aliases: []string{"t"},
		Usage:  "Put the application into maintenance mode",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name: "up",
		// Category: "app",
		// Aliases: []string{"t"},
		Usage:  "Bring the application out of maintenance mode",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name: "migrate",
		// Category: "app",
		// Aliases: []string{"t"},
		Usage:  "Run the database migrations",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "config:cache",
		Category: "config",
		// Aliases: []string{"t"},
		Usage:  "Cache app config",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "config:clear",
		Category: "config",
		// Aliases: []string{"t"},
		Usage:  "Clear cached configuration",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "config:json",
		Category: "config",
		// Aliases: []string{"t"},
		Usage:  "Return config as JSON",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "make:migration",
		Category: "make",
		// Aliases: []string{"t"},
		Usage:  "Generate a migration",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "make:api",
		Category: "make",
		// Aliases: []string{"t"},
		Usage:  "Generate an API endpoint (API class, model, events, DTO)",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "make:model",
		Category: "make",
		// Aliases: []string{"t"},
		Usage:  "Generate a model",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "migrate",
		Category: "migrate",
		// Aliases: []string{"t"},
		Usage:  "Run the database migrations",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "migrate:rollback",
		Category: "migrate",
		// Aliases: []string{"t"},
		Usage:  "Rollback last database migration",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "migrate:scaffold",
		Category: "migrate",
		// Aliases: []string{"t"},
		Usage:  "Scaffold database migrations (user, teams, ...)",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "migrate:status",
		Category: "migrate",
		// Aliases: []string{"t"},
		Usage:  "See the status of your migrations",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "route:cache",
		Category: "route",
		// Aliases: []string{"t"},
		Usage:  "Cache app routes",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "route:clear",
		Category: "route",
		// Aliases: []string{"t"},
		Usage:  "Clear cached routes",
		Action: start,
	})

	cliApp.Commands = append(cliApp.Commands, cli.Command{
		Name:     "storate:clear",
		Category: "storate",
		// Aliases: []string{"t"},
		Usage:  "Clear storage folder data",
		Action: start,
	})
}
