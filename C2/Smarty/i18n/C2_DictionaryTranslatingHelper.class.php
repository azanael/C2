<?php
require_once 'C2_InternationalizationException.class.php';
require_once 'C2_TranslatingInterface.class.php';

// For using double-quote in dictionary file.
define('Q', '"');

/**
 * 国際化（他言語翻訳）のヘルパ。
 * 辞書ファイルを読み込み、その中から翻訳後の文字列を探し出して返す。
 *
 * 辞書ファイルは{辞書格納ディレクトリ}/{辞書名}.lang というパスで保存する。
 * 中身はini形式で記載する。翻訳後のキーは元のキーに"_言語コード"とする。（下記サンプル参照）
 * 複数の辞書ファイルを読み込んだ場合はマージして扱われ、重複するキーが存在する場合は
 * 後から読み込んだキーが優先される。
 *
 * 辞書ファイルに登録されていない文字列を指定した場合、あるいは翻訳後のキーが
 * 見つからなかった場合は、元の文字列をそのまま返す。
 * （なので、もし一部の文字列が翻訳されない、といった現象が有れば辞書ファイルを確認すること）
 *
 * = 辞書ファイルのサンプル(default.lang) =
 *
 * ; コメントは;をつけて記載する。
 * [toppage] ; セクション指定は意味を持たないが、ファイルの見通しを良くするために記載しても良い
 * sample = "こんにちは" ; 文字列はかならず"(Double-Quote)で括る必要が有る
 * sample_en = 'Hello' ; sampleというキー名に_enをつけると、英語化。
 */
class C2_DictionaryTranslatingHelper implements C2_TranslatingHelperInterface
{
    const DEFAULT_DICTIONARY = 'default';
    const DICTIONALY_FILE_EXTENSION = 'lang';
    
    const DICTIONARY_DEBUG_MODE = false;
    
    private $translatingLang = null;
    private $dictionary;
    private $dictionaryBaseDir = null;
    private $loadedDictionaryList = array();
    
    /**
     * コンストラクタ
     *
     * @param $translatingLang どの言語に翻訳するか。'ja'や'en'など、2文字言語コードを指定
     * @param $dictionaryBaseDir 辞書ファイルのベースディレクトリ。最後の/は記載しない
     */
    public function __construct($translatingLang = 'en', $dictionaryBaseDir = null)
    {
        $this->translatingLang = $translatingLang;
        $this->dictionaryBaseDir = ($dictionaryBaseDir === null) ? 'lang' : $dictionaryBaseDir;
    }
    
    /**
     * どの言語に翻訳するかを返す
     */
    public function getTranslatingLang()
    {
        return $this->translatingLang;
    }
    
    /**
     * どの言語に翻訳するかを設定する
     */
    public function setTranslatingLang($translatingLang)
    {
        $this->translatingLang = $translatingLang;
    }
    
    /**
     * 辞書ファイルのベースディレクトリを返す
     */
    public function getDictionalyBaseDir()
    {
        return $this->dictionaryBaseDir;
    }
    
    /**
     * 辞書ファイルのベースディレクトリを設定する
     */
    public function setDictionalyBaseDir($dictionaryBaseDir)
    {
        $this->dictionaryBaseDir = $dictionaryBaseDir;
    }
    
    /**
     * 読み込み済みの辞書ファイル名を配列で返す
     */
    public function getLoadedDictionaryList()
    {
        return $this->loadedDictionaryList;
    }
    
    /**
     * 辞書ファイルを読み込む
     *
     * @param $dictionaryName 辞書名。辞書ファイルから拡張子を取った名前。
     * @param $require 辞書ファイルの読み込みを必須とするかどうか。trueでファイルが存在しない場合、例外を投げる。
     */
    public function loadDictionary($dictionaryName = null, $require = false)
    {
        if ($dictionaryName === null) {
            $dictionaryName = self::DEFAULT_DICTIONARY;
        }
        if (in_array($dictionaryName, $this->loadedDictionaryList) === true) {
            return true; // already loaded.
        }
        if (!file_exists($this->getDictionaryPath($dictionaryName))) {
            if ($require === true) {
                throw new C2_InternationalizationRuntimeException("Dictionary file not found. file=" . $this->getDictionaryPath($dictionaryName));
            }
            return false;
        }
        $dictionary = parse_ini_file($this->getDictionaryPath($dictionaryName));
        if ($dictionary === false) {
            throw new C2_InternationalizationRuntimeException('Failed to parse dictionary file. file=' . $this->getDictionaryPath($dictionaryName));
        }
        $this->dictionary = (is_array($this->dictionary)) ? array_merge($this->dictionary, $dictionary) : $dictionary;
        $this->loadedDictionaryList[] = $dictionaryName;
        return true;
    }
    
    /**
     * 辞書ファイルに則り、文字列を翻訳する
     *
     * このメソッドを実行する前にloadDictionaryで辞書ファイルを読み込んでおく必要がある。
     * 辞書ファイルに該当する文字列の記載が無い場合、あるいは翻訳後のキーが見つからなかった場合には
     * 引数で指定された文字列をそのまま返す。
     *
     * @param $message 翻訳する文字列
     */
    public function translate($message)
    {
        if ($this->translatingLang === null) {
            throw new C2_InternationalizationException('Translating language is null.');
        }
        if (empty($message)) {
            return '';
        }
        $message = str_replace('\"', '"', $message);
        if (empty($this->dictionary)) {
            return $message;
        }
        // dictionaryの改行コードが\r\nなので、翻訳するメッセージの改行コードを変換してからキーを探す
        $messageKeys = array_keys($this->dictionary, str_replace("\n", "\r\n", $message));
        foreach ($messageKeys as $key) {
            if (preg_match('/\_[a-z]+$/', $key)) {
                continue;
            }
            $messageKey = $key;
            break;
        }
        $translatedKey = $messageKey . '_' . $this->translatingLang;
        if ($messageKey === false || !isset($this->dictionary[$translatedKey])) {
            return str_replace('\"', '"', $message);
        }
        // preg_replaceがQuoteをエスケープしているので、エスケープを解除してから返す
        return str_replace('\"', '"', $this->dictionary[$translatedKey]);
    }
    
    /**
     * 読み込み済みの辞書ファイルをすべてクリアする
     */
    public function clearDictionaries()
    {
        $this->dictionary = null;
        $this->loadedDictionaryList = array();
    }

    private function getDictionaryPath($dictionaryName)
    {
        if (empty($dictionaryName)) {
            throw new C2_InternationalizationException('DictionaryName is empty.');
        }
        return $this->dictionaryBaseDir . '/' . $dictionaryName . '.' . C2_DictionaryTranslatingHelper::DICTIONALY_FILE_EXTENSION;
    }
}