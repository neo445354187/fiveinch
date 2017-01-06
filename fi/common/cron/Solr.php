<?php
namespace fi\common\cron;

use fi\common\helper\Browser;
use think\Db;

/**
 *
 */
class Solr
{

    /**
     * [$form_data post传递数据，true和false必需是字符串，坑]
     * @var [type]
     */
    private $form_data = [
        'command'  => 'delta-import',
        'verbose'  => 'false',
        'clean'    => 'false',
        'commit'   => 'true',
        'optimize' => 'false',
        'core'     => 'track',
        'name'     => 'dataimport',
    ];

    /**
     * [$params 拼装在url上的参数]
     * @var [type]
     */
    private $params = [
        '_'      => 0,
        'indent' => 'on',
        'wt'     => 'json',
    ];

    private $url = 'http://127.0.0.1:8983/solr/redundancy/dataimport';

    /**
     * [$is_hacker 判断是否是hacker]
     * @var boolean
     */
    private $is_hacker = false;

    public function __construct()
    {
        // if (php_sapi_name() != 'cli') {
        //     $this->is_hacker = true;
        //     \Think\Log::write('some one wants to enter solr by abnormal access', 'WARN');
        //     exit('fail');
        // }
        $this->params['_'] = ceil(microtime(true) * 1000);
        $this->url .= '?' . http_build_query($this->params);
    }

    /**
     * [fullImport 全量导入]
     * POST
     * http://127.0.0.1:8983/solr/track/dataimport?_=1482454267350&indent=on&wt=json
     * Query string params
     *     _:1482454267350
     *     indent:on
     *     wt:json
     * Form data
     *     command:full-import
     *     verbose:false
     *     clean:true
     *     commit:true
     *     optimize:false
     *     core:track
     *     name:dataimport
     * GET
     * http://127.0.0.1:8983/solr/track/dataimport?_=1482454267350&command=status&indent=on&wt=json
     *
     *
     * @return [type] [description]
     */
    public function fullImport()
    {
        $this->form_data = array_merge($this->form_data, ['command' => 'full-import', 'clean' => 'true']);
        //猜测：好像是异步
        Browser::curl($this->url, $this->form_data, false, 'POST');
        echo "completed";
    }

    /**
     * [deltaImport 增量导入]
     * POST
     * http://127.0.0.1:8983/solr/track/dataimport?_=1482454267350&indent=on&wt=json
     * Query string params
     *     _:148 245 426 7350
     *     indent:on
     *     wt:json
     * Form data
     *     command:delta-import
     *     verbose:false
     *     clean:false
     *     commit:true
     *     optimize:false
     *     core:track
     *     name:dataimport
     * @return [type] [description]
     */
    public function deltaImport()
    {
        Browser::curl($this->url, $this->form_data, false, 'POST');
        echo "completed";
    }

    /**
     * [clean 清除数据中的冗余]
     * @return [type]        [description]
     */
    public function __destruct()
    {
        if ($this->is_hacker) {
            return;
        }
        //不能换成model('Redundancy')哦，不然会与Redundancy模型相互调用死循环
        Db::name('redundancy')->where([
            'update_time' => ['ELT', date('Y-m-d H:i:s', time() - config('SOLR_INTERVAL') - 86400 * 30)],
            'is_delete'   => 1,
        ])->delete();
    }

}
