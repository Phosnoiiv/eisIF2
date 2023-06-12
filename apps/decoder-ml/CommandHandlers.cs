using AssetStudio;

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
}
