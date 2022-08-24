const Generator = require("yeoman-generator");
const path = require("path");

module.exports = class extends Generator
{
    constructor(args, options, features)
    {
        super(args, options, features);

        this.argument("name", {
            type: String,
            description: "Your plugin name",
            required: true
        });

        // this.option("editorconfig", {
        //     //alias: "",
        //     type: Boolean,
        //     description: "Include an .editorconfig file",
        //     default: true
        // });
    }

    initializing()
    {
        this.PROJECT_DIR = path.resolve(__dirname + "/../../");
        this.PLUGINS_DIR = path.resolve(this.PROJECT_DIR + "/plugins");

        // Override the destination path to force creation where we need it!
        this.destinationRoot(path.resolve(this.PLUGINS_DIR + "/" + this.options["name"]));

    }

    async prompting()
    {
        const answers = await this.prompt([
            {
                type: "confirm",
                name: "cool",
                message: "Would you like to enable the Cool feature?"
            }
        ]);

        //this.log("app name", answers.name);
        //this.log("cool feature", answers.cool);

    }

    configuring() {
    }

    default()
    {
        this.fs.copyFileSync("src/.editorconfig", "src/.editorconfig");


    }

    writing() {
    }

    conflicts() {
    }

    install() {
    }

    end() {
    }

};
