namespace Shock;
[Serializable]
internal class MusicMst {
    public uint _id;
    public string _name;
    public string _shortName;
    public string _kana;
    public string _artist;
    public string _detailInfo;
    public string _dictionaryReference;
    public string _dictionaryComment;
    public BAND_CATEGORY _bandCategory;
    public uint _masterGroupId;
    public string _jacketImageName;
    public uint _masterBgmId;
    public uint _previewMasterBgmId;
    public uint _locked;
    public OBTAIN_TYPE _obtainType;

    /// <remarks>Since 2.0.0-alpha.1.11 (Game 1.10.0)</remarks>
    public int _isAcLevelMusic;

    public string _releaseDateTime;
    public uint _masterReleaseLabelId;
}
[Serializable]
internal class MusicLevelMst {
    public uint _masterMusicId;
    public LIVE_LEVEL _level;
    public int _levelNumber;
    public string _noteDataFileName;
    public int _fullCombo;
    public int _beforeClimaxNotesCount;
    public float _scoreCoeff;
    public float _climaxScoreCoeff;
    public float _voltageIncreaseCoeff;
    public float _voltageDecreaseCoeff;
    public uint _masterReleaseLabelId;
}
[Serializable]
internal class LiveMst {
    public uint _id;
    public uint _masterMusicId;
    public CARD_TYPE _type;
    public int _scoreC;
    public int _scoreB;
    public int _scoreA;
    public int _scoreS;
    public int _multiScoreC;
    public int _multiScoreB;
    public int _multiScoreA;
    public int _multiScoreS;
    public int _liveEffectValueId;
    public int _bpm;
    public float _startWait;
    public float _endWait;
    public uint _masterLiveRewardSettingId;
    public uint _liveBgMovieMasterId;
    public string _rehearsalImagePath;
    public int _priority;
    public int _campaignFlag;
    public uint _masterReleaseLabelId;
}
