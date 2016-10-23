<?php
/**
 * Author: 0x584A
 * Email: xjiek2010 [at] icloud.com
 * Time: 2016-10-23 08:32
 */

require __DIR__ . '/vendor/autoload.php';
use \Curl\Curl;

class tools
{
    protected static $value1 = 'u:t:m:f:d:c:h:n'; // 参数1
    protected static $value2 = array('help'); // 参数2
    protected static $url; // url地址
    protected static $method = 'get'; // 表单提交方式
    protected static $type; // 类型
    protected static $cookie; // 免登录用cookie
    protected static $filedir; // 字典地址
    protected static $postData; // 表单提交数据
    protected static $headers; // 额外消息头
    protected static $norm; // 提交信息标准
    protected static $payload; // POC
    protected static $GREEN_COLOR; // 输出字体背景颜色及字体颜色
    protected static $RES = '\033[0m'; // 结束
    protected static $USER_AGENTS = [
        "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; AcooBrowser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Acoo Browser; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.0.04506)",
        "Mozilla/4.0 (compatible; MSIE 7.0; AOL 9.5; AOLBuild 4337.35; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
        "Mozilla/5.0 (Windows; U; MSIE 9.0; Windows NT 9.0; en-US)",
        "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)",
        "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)",
        "Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 5.2; .NET CLR 1.1.4322; .NET CLR 2.0.50727; InfoPath.2; .NET CLR 3.0.04506.30)",
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN) AppleWebKit/523.15 (KHTML, like Gecko, Safari/419.3) Arora/0.3 (Change: 287 c9dfb30)",
        "Mozilla/5.0 (X11; U; Linux; en-US) AppleWebKit/527+ (KHTML, like Gecko, Safari/419.3) Arora/0.6",
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.2pre) Gecko/20070215 K-Ninja/2.1.1",
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/20080705 Firefox/3.0 Kapiko/3.0",
        "Mozilla/5.0 (X11; Linux i686; U;) Gecko/20070322 Kazehakase/0.4.5",
        "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.8) Gecko Fedora/1.9.0.8-1.fc10 Kazehakase/0.5.6",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/535.20 (KHTML, like Gecko) Chrome/19.0.1036.7 Safari/535.20",
        "Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; fr) Presto/2.9.168 Version/11.52",
    ]; // 随机用户环境

    public static function main()
    {
        if (empty($args = getopt(self::$value1, self::$value2))) {
            // 详细帮助说明
            $MyLogo = file_get_contents(__DIR__ . "/0x584a.txt");
            print $MyLogo . PHP_EOL;
            print PHP_EOL . "\t[-] 参数说明：" . PHP_EOL;
            print "\t[-] -u : 要攻击检测有效url地址" . PHP_EOL;
            print "\t[-] -t : 要攻击检测的类型:现在只支持Xss与混合扫秒" . PHP_EOL;
            print "\t[-] -f : 扫描字典路径及文件" . PHP_EOL;
            print "\t[-] -m : 表单提交模式get/post" . PHP_EOL;
            print "\t[-] -d : 表单提交数据,举例：'user=admin&password=admin'" . PHP_EOL;
            print "\t[-] -n : 表单提交类型:json,text,form,x-www-form-urlencoded" . PHP_EOL;
            print "\t[-] -c : 免登录用cookie" . PHP_EOL;
            print "\t[-] -h : 额外消息头信息,举例：'Accept:text/javascript|Accept-Language:zh-CN,zh|....',多个请用'|'符号隔开" . PHP_EOL;
        } else {
            self::init($args);
        }
    }

    public static function init($args)
    {
        if (isset($args['help'])) {
            self::help();
        }
        // 组合校检
        self::isParameter($args);
        self::RequestData(self::$type);
    }

    public static function isParameter($args)
    {
        if ( !isset($args['u']) || empty($args['u'])) {
            self::error('Error', '请输入\'-u\'，URL参数没有也想公鸡?');
        }

        if (isset($args['t']) != 'xss' || isset($args['t']) != 'dir') {
            self::error('Error', '请输入\'-t\'，选择所需模式 xss or dir .');
        }

        if (isset($args['m'])) {
            if (empty($args['d'])) {
                self::error('Error', '请输入\'-d\'，你要提交的登录参数未填写!');
            }
        }

        self::$url = $args['u'];
        self::$type = $args['t'];
        if (isset($args['m'])) {
            self::$method = $args['m'];
            self::$postData = $args['d'];
            self::$norm = empty($args['n']) ? 'text' : $args['n']; // 数据提交格式
            self::$headers = empty($args['n']) ? '' : $args['h']; // 自定义消息头
        }
        if (isset($args['c'])) {
            self::$cookie = $args['c'];
        }
        self::$filedir = empty($args['f']) ? __DIR__ . '/xssfile.txt' : $args['f'];
    }

    public static function error($type, $msg)
    {
        switch ($type) {
            case 'Error':
                self::$GREEN_COLOR = '\033[41;37m'; // 红底白字
                echo shell_exec('echo "\n' . self::$GREEN_COLOR . sprintf("[%s] : %s", $type, $msg) . self::$RES . '"');
                self::help();
                break;
            case 'Info':
                self::$GREEN_COLOR = '\033[42;37m'; // 绿底白字
                echo shell_exec('echo "\n' . self::$GREEN_COLOR . sprintf("[%s] : %s", $type, $msg) . self::$RES . '"');
                break;
            default:
                break;
        }
        print PHP_EOL . PHP_EOL;
        exit();
    }

    /**
     * 发送Get请求
     */
    public static function RequestData($type)
    {
        switch ($type) {
            case 'xss':
                echo "[+] xss模糊检测模式" . PHP_EOL;
                // 检测页面源代码是否出现exp
                self::do_xss_query();
                break;
            case 'dir':
                echo "[+] 路径检测模式:" . PHP_EOL;
                // 检测页面相应状态及相应字符长度
                self::do_dir_query();
                break;
            default:
                echo "[+] 请选择模式 [ -t xss ] or [ -t dir] !!! " . PHP_EOL;
                exit();
                break;
        }
    }

    // 用于xss检测
    public static function do_xss_query()
    {
        $curl = new Curl();
        $filedata = @fopen(self::$filedir, "r");
        if ($filedata) {
            while (($buffer = fgets($filedata, 4096)) !== false) {
                // 额外消息头
                if ( !empty(self::$headers) && is_array(self::$headers)) {
                    $curl->setHeaders(self::$headers);
                }
                // 传递的cookie
                if ( !empty(self::$cookie) && is_string(self::$cookie)) {
                    $curl->setCookieString(self::$cookie);
                }

                $tmpStr = self::$USER_AGENTS[ array_rand(self::$USER_AGENTS, 1) ];
                $curl->setUserAgent($tmpStr);

                // 判断提交方式是get还是post
                $method = self::$method;
                switch ($method) {
                    case 'post':
                        $data = []; $tmpStr = '无';
                        // 解析表单参数
                        $postData = explode('&',self::$postData);
                        foreach ($postData as $key){
                            $tmpArr = explode('=',$key);
                            if (count($tmpArr) < 2){
                                self::error('Error','提交参数，或数据格式错误');
                            }
                            $data[$tmpArr[0]] = $tmpArr[1];
                        }
                        $curl->$method(self::$url, $data);
                        foreach ($curl->responseHeaders as $k => $v) {
                                if ($k == 'Set-Cookie'){
                                    $tmpStr = $v;
                                }
                        }
                        self::error('Info','登录后的cookies -> '.$tmpStr);
                        break;
                    case 'get':
                        $curl->$method(trim(self::$url . $buffer));
                        break;
                    default:
                        self::error('Error', '错误的模式,暂只支持get/post');
                        break;
                }
                if ($Request = $curl->response) {
                    if ($x = mb_stristr($Request, urldecode(trim($buffer)), false, 'utf-8')) {
                        self::$payload = trim($buffer);
                        break;
                    }
                } else {
                    self::error('Error', sprintf('请求异常，或无响应 => %s , %s', $curl->errorCode, $curl->errorMessage));
                }
            }
            fclose($filedata);
            if ( !empty(self::$payload)) {
                print PHP_EOL . "发现可利用payload:" . self::$payload . PHP_EOL;
            } else {
                self::error('Info', '未发现可用payload!');
            }
            self::error('Info', '扫描结束!');
        }
    }

    public function do_dir_query()
    {
        echo "\n瞄~ 让我懒一会，下次在写...";
    }

    /**
     * 帮助
     */
    public static function help()
    {
        $MyLogo = file_get_contents(__DIR__ . "/0x584a.txt");
        print $MyLogo . PHP_EOL;
        print "[+] author:0x584A" . PHP_EOL;
        print "[+] link:jgeek.cn" . PHP_EOL;
        print "[+] version:v1.0" . PHP_EOL;
        print "[+] description: 用于Url的XSS检测与混合模扫描。" . PHP_EOL;
        print "[+] 参数说明：输入参数--help" . PHP_EOL;
        print PHP_EOL . "[+] 使用说明：" . PHP_EOL;
        print PHP_EOL . "\t[-] exp[0]: php tools.php -t xss -f ./xssfile.txt -u 'www.test.com/index.php?test=' ";
        print PHP_EOL . "\t[-] exp[1]: php tools.php -t dir -f ./dirfile.txt -u 'www.test.com/index.php?test=' ";
        print PHP_EOL . "\t --- 增对特殊需要登录的方式，首先获得登录后的Cookie才进行检测。 --- ";
        print PHP_EOL . "\t[-] exp[0]: php tools.php -t [xss or dir] -m [get or post] -u 'www.test.com/login.php' -d 'user=0x584A&passwrod=0x584A' ";
        print PHP_EOL . "\t[-] exp[1]: php tools.php -t [xss or dir] -m [get or post] -u 'www.test.com/index.php?test=' -c '此处填写所需要的cookie'" . PHP_EOL;
        exit();
    }
}

// 入口
tools::main();
