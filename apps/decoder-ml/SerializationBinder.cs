namespace EverISay.SIF.ML.Decoder;
internal class SerializationBinder:System.Runtime.Serialization.SerializationBinder {
    public override Type? BindToType(string assemblyName, string typeName) {
        return Type.GetType(typeName);
    }
}
