/**
 * Class Segmentation
 */
class Segmentation
{
    protected array $strArr = [];
    protected ?Redis $redis = null;

    const SUPPORT_COMMAND_TAG = 'tag';
    const SUPPORT_COMMAND_CUT = 'cut';
    const SUPPORT_COMMAND_CUT_ALL = 'cutall';
    const SUPPORT_COMMAND_ADD_WORD = 'addword';
    const SUPPORT_COMMAND_CUT_FOREACH = 'cutforsearch';
    const SUPPORT_COMMAND_SELECT = 'SELECT';
    const SUPPORT_COMMAND_PING = 'ping';
    const SUPPORT_COMMAND_VERSION = 'version';

    public function __construct()
    {
    }

    /**
     * 设置redis
     * @param string $ip
     * @param int $port
     * @param int $timeout
     * @return $this
     */
    public function setRedis(string $ip = '127.0.0.1', int $port = 6380, int $timeout = 10): self
    {
        //连接redis
        $this->redis = new Redis();
        $this->redis->connect($ip, $port, $timeout);
        $this->redis->select(0);
        return $this;
    }

    /**
     * 进行分词拆分
     * @param string $command
     * @param string $str
     * @param int $flag
     * @return $this
     * @throws Exception
     */
    public function segment(string $command, string $str, int $flag = 1): self
    {
        if (is_null($this->redis)) {
            throw new Exception('please set redis before segment!');
        }
        if (!in_array($command, [
            self::SUPPORT_COMMAND_ADD_WORD,
            self::SUPPORT_COMMAND_CUT,
            self::SUPPORT_COMMAND_CUT_ALL,
            self::SUPPORT_COMMAND_CUT_FOREACH,
            self::SUPPORT_COMMAND_PING,
            self::SUPPORT_COMMAND_TAG,
            self::SUPPORT_COMMAND_VERSION,
            self::SUPPORT_COMMAND_SELECT
        ])) {
            throw new Exception('unsupported command!');
        }
        $this->strArr = $this->redis->rawCommand($command, $str, $flag);
        return $this;
    }

    /**
     * 获取拆分后的数组
     * @return array
     */
    public function getSegmentedArr(): array
    {
        return $this->strArr;
    }

    /**
     * Cosinθ余弦夹角计算两个字符串相似度
     * @param string $s1
     * @param string $s2
     * @return float
     * @throws Exception
     */
    public function similar(string $s1, string $s2): float
    {
        $strArr1 = $this->setRedis()
            ->segment(self::SUPPORT_COMMAND_CUT_FOREACH, $this->delSymbol($s1))
            ->getSegmentedArr();
        $strArr2 = $this->setRedis()
            ->segment(self::SUPPORT_COMMAND_CUT_FOREACH, $this->delSymbol($s2))
            ->getSegmentedArr();
        $wordArr = array_unique(array_merge($strArr1, $strArr2));
        $vectorStr1 = $this->getVectorStr($strArr1, $wordArr);
        $vectorStr2 = $this->getVectorStr($strArr2, $wordArr);

        //计算相似度
        $sum = $sumT1 = $sumT2 = 0;
        foreach ($wordArr as $key => $temp) {
            $sum += $vectorStr1[$key] * $vectorStr2[$key];
            $sumT1 += pow($vectorStr1[$key], 2);
            $sumT2 += pow($vectorStr2[$key], 2);
        }
        return $sum / (sqrt($sumT1) * sqrt($sumT2));
    }

    /**
     * 生成词频向量数组
     * @param $strArr
     * @param $wordArr
     * @return array
     */
    public function getVectorStr($strArr, $wordArr): array
    {
        $vectorStr = [];
        foreach ($wordArr as $key1 => $temp2) {
            $num = 0;
            foreach ($strArr as $key2 => $temp1) {
                if ($temp2 == $temp1) {
                    $num++;
                }
            }
            $vectorStr[$key1] = $num;
        }
        return $vectorStr;
    }

    /**
     * 去除字符串中的 多余符合提高准确率
     * @param $str
     * @return string
     */
    public function delSymbol($str): string
    {
        $symbolArr = ['​', '“', '”', '"', '>', '<', ' ', ' ', '`', '·', '~', '!', '！', '@', '#', '$', '￥',
            '%', '^', '……', '&', '*', '(', ')', '（', '）', '-', '_', '——', '+', '=', '|', '\\', '[', ']', '【', '】',
            '{', '}', ';', '；', ':', '：', '\'', '"', '“', '”', ',', '，', '<', '>', '《', '》', '.', '。', '/', '、',
            '?', '？'];
        return str_replace($symbolArr, '', $str);
    }
}

//$str1 = $argv[1] ?? '';
//$str2 = $argv[2] ?? '';
//$obj2 = new Segmentation();
//$arr = $obj2->setRedis()->segment("cutforsearch", $str1);
//var_dump($arr);
//$s = $obj2->similar($str1, $str2);
//var_dump($s);
?>