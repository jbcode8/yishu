<?php
// +----------------------------------------------------------------------
// | AdminController.class.php.
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------
namespace Malladmin\Controller;
use Admin\Model\AuthRuleModel;
use Think\Auth;
use Think\Controller;

/**
 * 后台控制器
 * @package Admin\Controller
 */
class AdminController extends Controller {

    /**
     * 初始化后台控制器
     */
    protected function _initialize(){

        define('UID',admin_is_login());
        if(!UID){ //如果没有登录则跳转登录页面
			echo '<script>'."top.location.href = '".U('Admin/Public/login')."'".'</script>'; exit;
            //$this->redirect();
        }

        C(api('Admin/Config/lists',array('group'=>0)));

        //定义判断是否为超级管理员
        define('IS_ROOT',is_administrator());

        $dynamic = $this->checkDynamic();//检测分类栏目有关的各项动态权限
        if( $dynamic === null ){
            //检测非动态权限
            if(strtolower(MODULE_NAME) != 'admin' && strtolower(CONTROLLER_NAME) != 'index'){
                $rule  = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
                if ( !$this->checkRule($rule) ){
                    $this->error('提示:无权访问,您可能需要联系管理员为您授权!');
                }
            }
        }elseif( $dynamic === false ){
            $this->error('提示:您无权访问,请与管理员联系,并给予您授权!');
        }

        $this->assign('__controller__',$this);
    }

    /**
     * 检测权限
     * @param $rule
     * @param $type
     * @param string $mode
     * @return bool
     */
    final protected function checkRule($rule, $type=AuthRuleModel::RULE_URL, $mode='url'){
        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }
        static $Auth    =   null;
        if (!$Auth) {
            $Auth       =   new Auth();
        }
        if(!$Auth->check($rule,UID,$type,$mode)){
            return false;
        }
        return true;
    }

    /**
     * 动态检测权限 如为超级管理员则路过动态验证
     * @return bool
     */
    final protected function checkDynamic(){
        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }
        return null;//不明,需checkRule
    }

    /**
     * 通用分页列表数据集获取方法,获取的数据集主要供tableList()方法用来生成表格列表
     *
     *  可以通过url参数传递where条件,例如:  index.html?name=asdfasdfasdfddds
     *  可以通过url空值排序字段和方式,例如: index.html?_field=id&_order=asc
     *  可以通过url参数r指定每页数据条数,例如: index.html?r=5
     *
     * @param sting|Model $model 模型名或模型实例
     * @param array $where where查询条件(优先级: $where>$_REQUEST>模型设定)
     * @param array|string $order 排序条件,传入null时使用sql默认排序或模型属性(优先级最高);
     *                              请求参数中如果指定了_order和_field则据此排序(优先级第二);
     *                              否则使用$order参数(如果$order参数,且模型也没有设定过order,则取主键降序);
     *
     * @param array $base 基本的查询条件
     * @param boolean $field 单表模型用不到该参数,要用在多表join时为field()方法指定参数
     *
     * @param bool $limit 是否开启分页,默认开启
     * @return array|false
     * 返回数据集
     */
    protected function lists ($model,$where=array(),$order=null,$base = array(),$field=true,$limit=true){
        $options    =   array();
        $REQUEST    =   (array)I('request.');
        if(is_string($model)){
            $model  =   D($model);
        }

        $OPT        =   new \ReflectionProperty($model,'options');
        $OPT->setAccessible(true);

        $pk         =   $model->getPk();
        if($order===null){
            //order置空
        }else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
            $options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
        }elseif( $order==='' && empty($options['order']) && !empty($pk) ){
            $options['order'] = $pk.' desc';
        }elseif($order){
            $options['order'] = $order;
        }
        unset($REQUEST['_order'],$REQUEST['_field']);

        $options['where'] = array_filter(array_merge( (array)$base, $REQUEST, (array)$where ),function($val){
            if($val===''||$val===null){
                return false;
            }else{
                return true;
            }
        });

        $options      =   array_merge( (array)$OPT->getValue($model), $options );
        $total        =   $model->where($options['where'])->count();

        if( isset($REQUEST['r']) ){
            $listRows = (int)$REQUEST['r'];
        }else{
            $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        }
        $page = new \Think\Page($total, $listRows, $REQUEST);
        if($total>$listRows){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $p =$page->show();
        $this->assign('_page', $p? $p: '');
        $this->assign('_total',$total);
        if($limit)
            $options['limit'] = $page->firstRow.','.$page->listRows;

        $model->setProperty('options',$options);

        return $model->field($field)->select();
    }

    /**
     * 数据集分页
     * @param array $records 传入的数据集
     */
    public function recordList($records){
        $request    =   (array)I('request.');
        $total      =   $records? count($records) : 1 ;
        if( isset($request['r']) ){
            $listRows = (int)$request['r'];
        }else{
            $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        }
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($records, $page->firstRow, $page->listRows);
        $p			=	$page->show();
        $this->assign('_list', $voList);
        $this->assign('_page', $p? $p: '');
    }

    /**
     * 后台通用生成表格列表
     * @param $list 需要生成 table 的数组
     * @param $thead 表头及生成的配置项
     * @return string
     */
    public function tableList( $list, $thead ){
        $list = (array)$list;
        if(APP_DEBUG){
            //debug模式检测数据
            $List  = new \RecursiveArrayIterator($list);
            $RList = new \RecursiveIteratorIterator($List,\RecursiveIteratorIterator::CHILD_FIRST);
            foreach($RList as $v){
                if($RList->getDepth()==2){
                    //数据集不是二维数组
                    die('<h1>'.'严重问题：表格列表数据集参数不是二维数组'.'</h1>');
                    break;
                }
            }

            $keys   =   array_keys( (array)reset($list) );
            foreach($list as $row){
                $keys = array_intersect( $keys, array_keys($row) );
            }
            $s_thead =  serialize($thead);
            if(!empty($list)){
                preg_replace_callback('/\$([a-zA-Z_]+)/',function($matches) use($keys){
                    if( !in_array($matches[1],$keys) ){
                        die('<h1>'.'严重问题：数据列表表头定义使用了数据集中不存在的字段:$'.$matches[1].', 请检查表头和数据集.</h1>');
                    }
                },$s_thead);
            }
        }
        $keys       =   array_keys($thead);//表头所有的key
        array_walk($list,function(&$v,$k) use($keys,$thead) {
            $arr    =   array();//保存数据集字段的值
            foreach ($keys as $value){
                //判断表头key是否在数据集中存在对应字段
                if ( isset($v[$value]) ) {
                    $arr[$value] = $v[$value];
                }elseif( strpos($value,'_')===0 ){
                    $arr[$value] = @$thead[$value]['td'];
                }elseif( isset($thead[$value]['_title']) ){
                    $arr[$value] = '';
                }
            }
            $v      =   array_merge($arr,$v);//根据$arr的顺序更新数据集字段顺序
        });
        $this->assign('_thead',$thead);
        $this->assign('_list',$list);
        return $this->fetch(APP_PATH.'Admin/View/Public/_list.html');
    }

    /**
     * 添加
     */
    public function add($modelName = CONTROLLER_NAME){
        if(IS_POST){
            $model = D($modelName);
            if ($model->create()) {
                if ($model->add() !== false) {
                    $this->success('添加成功！',U('/baike/category'));
                } else {
                    $this->error('添加失败！');
                }
            } else {
                $this->error($model->getError());
            }
        }else{
            $this->display();
        }
    }

    /**
     * 修改
     */
    public function edit($modelName = CONTROLLER_NAME){
        $model = D($modelName);
        if(IS_POST){
            if ($model->create()) {
                if ($model->save() !== false) {
                    $this->success('更新成功！',U('/baike/category'));
                } else {
                    $this->error("数据没有任何改动!");
                }
            } else {
                $this->error($model->getError());
            }
        }else{
            $pk = $model->getPk();
            $id = I('get.'.$pk,'','intval');
            if (!empty($id)) {
                if ($data = $model->find($id)) {
                    $this->assign('vo',$data);
                } else {
                    $this->error('数据不存在！');
                }
            } else {
                $this->error('参数有误!');
            }
            $this->display();
        }
    }

    /**
     * 使用get方式 单条记录删除
     * 使用post方式 多条记录删除
     */
    public function delete($modelName = CONTROLLER_NAME){
        $model = D($modelName);
        $pk = $model->getPk();
        if(IS_POST){
            $map[$pk] = array('IN',I('post.'.$pk));
            if(I('post.'.$pk)){
                if($model->where($map)->delete() !== FALSE){
                    $this->success('删除成功!');
                }else{
                    $this->error('删除失败!');
                }
            }else{
                $this->error('请选中需要删除的记录!');
            }
        }else{
            $id = I('get.'.$pk,'','intval');
            if ($id) {
                if ($model->delete($id)) {
                    $this->success('删除成功！');
                } else {
                    $this->error('删除失败！');
                }
            }else{
                $this->error('参数错误!');
            }
        }
    }

    /**
     * 排序
     */
    public function listorder($modelName = CONTROLLER_NAME){
        $model = D($modelName);
        $pk = $model->getPk();
        foreach ($_POST['listorder'] as $id => $v) {
            $condition = array($pk => $id);
            $model->where($condition)->setField('listorder', $v);
        }
        $this->success('更新排序成功');
    }

    /**
     * 禁用条目
     * @param string $model 模型名称,供D函数使用的参数
     * @param $pk 主键字段名
     * @param array $where 查询时的 where()方法的参数
     * @param array $msg 执行正确和错误的消息,可以设置四个元素 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
     *                     url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
     */
    protected function forbid ( $model,$pk , $where = array() , $msg = array( 'success'=>'状态禁用成功！', 'error'=>'状态禁用失败！')){
        $data    =  array('status' => 0);
        $this->editRow( $model,$pk , $data, $where, $msg);
    }

    /**
     * 恢复条目
     * @param string $model 模型名称,供D函数使用的参数
     * @param $pk 主键字段名
     * @param array $where 查询时的where()方法的参数
     * @param array $msg 执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
     *                     url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
     */
    protected function resume (  $model,$pk , $where = array() , $msg = array( 'success'=>'状态恢复成功！', 'error'=>'状态恢复失败！')){
        $data    =  array('status' => 1);
        $this->editRow(   $model,$pk , $data, $where, $msg);
    }

    /**
     * 还原条目
     * @param string $model 模型名称,供D函数使用的参数
     * @param $pk 主键字段名
     * @param array $where 查询时的where()方法的参数
     * @param array $msg 执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
     *                     url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
     */
    protected function restore (  $model ,$pk, $where = array() , $msg = array( 'success'=>'状态还原成功！', 'error'=>'状态还原失败！')){
        $data    = array('status' => 1);
        $where   = array_merge(array('status' => -1),$where);
        $this->editRow(   $model ,$pk, $data, $where, $msg);
    }

    /**
     * 对数据表中的单行或多行记录执行修改 GET参数id为数字或逗号分隔的数字
     *
     * @param string $model 模型名称,供M函数使用的参数
     * @param $pk 主键字段名
     * @param array $data 修改的数据
     * @param array $where 查询时的where()方法的参数
     * @param array $msg 执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
     *                     url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
     */
    final protected function editRow ( $model,$pk ,$data, $where , $msg ){
        $id    = array_unique((array)I($pk,0));
        $id    = is_array($id) ? implode(',',$id) : $id;
        $where = array_merge( array($pk => array('in', $id )) ,(array)$where );
        $msg   = array_merge( array( 'success'=>'操作成功！', 'error'=>'操作失败！', 'url'=>'' ,'ajax'=>IS_AJAX) , (array)$msg );
        if( M($model)->where($where)->save($data)!==false ) {
            $this->success($msg['success'],$msg['url'],$msg['ajax']);
        }else{
            $this->error($msg['error'],$msg['url'],$msg['ajax']);
        }
    }
}