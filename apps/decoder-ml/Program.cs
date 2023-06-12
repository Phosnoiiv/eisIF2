using EverISay.SIF.ML.Decoder;
using System.CommandLine;

var cmdRoot = new RootCommand();

var argBundle = new Argument<string>("bundle");
var argName = new Argument<string>("name");
var argExportPath = new Argument<string>("exportPath");

var cmdExport = new Command("export");
cmdExport.AddArgument(argBundle);
cmdExport.AddArgument(argName);
cmdExport.AddArgument(argExportPath);
cmdExport.SetHandler(CommandHandlers.Export, argBundle, argName, argExportPath);
cmdRoot.AddCommand(cmdExport);

return cmdRoot.Invoke(args);
