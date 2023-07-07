using EverISay.SIF.ML.Decoder;
using System.CommandLine;

var cmdRoot = new RootCommand();

var argInput = new Argument<string>("input");
var argBundle = new Argument<string>("bundle");
var argName = new Argument<string>("name");
var argPassword = new Argument<string>("password");
var argSalt = new Argument<string>("salt");
var argOutput = new Argument<string>("output");
var argExportPath = new Argument<string>("exportPath");

var cmdExport = new Command("export");
cmdExport.AddArgument(argBundle);
cmdExport.AddArgument(argName);
cmdExport.AddArgument(argExportPath);
cmdExport.SetHandler(CommandHandlers.Export, argBundle, argName, argExportPath);
cmdRoot.AddCommand(cmdExport);

var cmdDeserialize = new Command("deserialize");
cmdDeserialize.AddArgument(argInput);
cmdDeserialize.AddArgument(argOutput);
cmdDeserialize.SetHandler(CommandHandlers.Deserialize, argInput, argOutput);
cmdRoot.AddCommand(cmdDeserialize);

var cmdTableKey = new Command("tableKey");
cmdTableKey.AddArgument(argPassword);
cmdTableKey.AddArgument(argSalt);
cmdTableKey.SetHandler(CommandHandlers.GetTableKey, argPassword, argSalt);
cmdRoot.AddCommand(cmdTableKey);

return cmdRoot.Invoke(args);
