[Serializable]
internal class ShockBinaryBundleManifest {
    public string m_identifier;
    public string m_name;
    public /* Hash128 */ string m_hash;
    public uint m_crc;
    public long m_length;
    public string[] m_dependencies;
    public string[] m_labels;
    public string[] m_assets;
}

[Serializable]
internal class ShockBinaryBundleSingleManifest {
    public ShockBinaryBundleManifest[] m_manifestCollection;
}
