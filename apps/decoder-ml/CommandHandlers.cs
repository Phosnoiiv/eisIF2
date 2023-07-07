using AssetStudio;
using System.Runtime.Serialization.Formatters.Binary;
using System.Security.Cryptography;
using System.Text.Json;

namespace EverISay.SIF.ML.Decoder;
internal static class CommandHandlers {
    internal static void Export(string bundle, string name, string exportPath) {
        var manager = new AssetsManager();
        manager.LoadFiles(bundle);
        foreach (var file in manager.assetsFileList) {
            foreach (var asset in file.Objects) {
                if (asset is NamedObject namedAsset) {
                    if (namedAsset.m_Name != name) continue;
                    var ext = "";
                    byte[] bytes;
                    switch (namedAsset) {
                        case TextAsset textAsset:
                            // TODO: Support Container
                            bytes = textAsset.m_Script;
                            break;
                        default:
                            continue;
                    }
                    var path = Path.Combine(exportPath, name + ext);
                    File.WriteAllBytes(path, bytes);
                    return;
                }
            }
        }
    }

    internal static void Deserialize(string input, string output) {
        using var fileStream = new FileStream(input, FileMode.Open);
        var formatter = new BinaryFormatter {
            Binder = new SerializationBinder(),
        };
#pragma warning disable SYSLIB0011
        var result = formatter.Deserialize(fileStream);
#pragma warning restore SYSLIB0011
        var json = JsonSerializer.Serialize(result, new JsonSerializerOptions {
            IncludeFields = true,
        });
        File.WriteAllText(output, json);
    }

    internal static void GetTableKey(string password, string salt) {
        var crypt = new Rfc2898DeriveBytes(password, Convert.FromBase64String(salt));
        Console.Write(Convert.ToBase64String(crypt.GetBytes(16)));
    }
}
