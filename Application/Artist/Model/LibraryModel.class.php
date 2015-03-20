<?php
// +----------------------------------------------------------------------
// | 艺术家模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Artist\Model;

class LibraryModel extends ArtistModel{
    protected $_link = array(
        'works'=>array(
        'mapping_type'=> self::HAS_MANY,
        'class_name'=>'works',
        'mapping_name'=>'works',
        'foreign_key'=>'aid',
        'mapping_fields'=>'name',
        'as_fields'=>'name:workname',
        ),
        'category'=>array(
            'mapping_type'=> self::BELONGS_TO,
            'class_name'=>'category',
            'mapping_name'=>'category',
            'foreign_key'=>'cid',
            'mapping_fields'=>'name',
            'as_fields'=>'name:catename',
        ),
    );
    protected $_validate = array(
        array('name', 'require', '姓名不能为空', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
        array('sex', 'require', '性别不能为空', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
        array('thumb', 'require', '没有头像', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
        array('cid', 'require', '类别不能为空', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
        array('mobile', '/^(1(([35][0-9])|(47)|[8][01236789]))\d{8}$/', '手机号码格式有误', self::VALUE_VALIDATE, 'regex',self::MODEL_BOTH ),
        array('email', 'email', 'email格式有误', self::VALUE_VALIDATE, '',self::MODEL_BOTH ),
        array('email', '', 'email已经被使用了', self::VALUE_VALIDATE, 'unique',self::MODEL_INSERT ),
        array('qq', '/^[1-9][0-9]{4,9}$/', 'qq格式有误', self::VALUE_VALIDATE, 'regex',self::MODEL_BOTH ),
        array('qq', '', 'qq已经被使用了', self::VALUE_VALIDATE, 'unique',self::MODEL_INSERT ),
        array('type', array(0,1,2), '艺术家类型/有误', self::VALUE_VALIDATE, 'in',self::MODEL_BOTH ),
        array('web', 'url', '个人网页地址有误', self::VALUE_VALIDATE, '',self::MODEL_BOTH ),
        array('web', '', '个人网页地址已经被使用了', self::VALUE_VALIDATE, 'unique',self::MODEL_INSERT ),
        array('fax', '/^[+]{0,1}(\d){1,3}[ ]?([-]?((\d)|[ ]){1,12})+$/', '传真地址格式有误', self::VALUE_VALIDATE, 'regex',self::MODEL_BOTH ),
        array('description', 'require', '描述不能为空', self::VALUE_VALIDATE, '',self::MODEL_BOTH ),
        array('uid', 'checkUid', '用户id标识有误', self::MUST_VALIDATE, 'callback',self::MODEL_BOTH),
        array('birthday', '1900-01-01,2014-01-01', '生日格式有误', self::MUST_VALIDATE, 'between', self::MODEL_INSERT),
        array('jointime', '1900-01-01,2014-01-01', '入行时间格式有误', self::MUST_VALIDATE, 'between', self::MODEL_INSERT),
    );

    protected $_auto = array(
        array('pictureurls', 'serializeImg', self::MODEL_BOTH, 'callback'),
        array('createtime', 'time', self::MODEL_INSERT, 'function'),
        array('updatetime', 'time', self::MODEL_UPDATE, 'function'),
        array('letter', 'getFirstLetter', self::MODEL_BOTH, 'callback'),
        array('hits', 0, self::MODEL_INSERT, 'string'),
    );

    public function getFirstLetter(){
        return get_letter($_POST['name']);
    }

    public function serializeImg(){
        if(isset($_POST['pictureurls']))
            return serialize($_POST['pictureurls']);
        return '';
    }

    public function checkUid(){
        $uid = I('post.uid', '', 'intval');
        $operate = I('post.operate', '', 'intval');
        if($operate == 1 && $uid){//添加操作
            $count = M('Member')->where(array('uid'=>$uid))->count();
            if($count){
                $count = $this->where(array('uid'=>$uid))->count();
                if(!$count) return true;
             }

        }
        if($operate == 2 && $uid){//修改操作
           $count = $this->where(array('uid'=>$uid))->count();
           if($count) return true;
        }
        return false;
    }

    /**
     * 获取大师
     * @param  integer $limit 大师数
     * @param  array $map 查询条件
     * @param  array $order 查询条件
     * @return array
     */
    public function getLibrary($limit, $map, $order)
    {
        $library_list = $this->field('id,name,recordid,cid,goodat,description,view,jointime,birthday')->where($map)->order($order)->limit($limit)->select();
        foreach($library_list as $k=>$v) {
            $library_list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        return $library_list;
    }
    /**
     * 获取本周之星
     * @param  integer $limit 大师数
     * @param  array $map 查询条件
     * @return array
     */
    public function getWeekStar($limit, $map)
    {
        //获取大师信息
        $weekStar = $this->field('id,name,recordid,goodat,jointime,description')->where($map)->order('createtime DESC')->limit($limit)->select();
        foreach($weekStar as $k=>$v) {
            $weekStar[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        //获取大师下的作品信息
        foreach($weekStar as $k => $v){
            $weekStar[$k]['works'] = M('ArtistWorks')->field('id, recordid, name, aid')->where(array('aid' => $v['id'],'status'=>1,'thumb'=>1))->order('hits DESC')->limit(4)->select();
        }
        for($i=0,$count = count($weekStar);$i<$count;$i++) {
            for($j=0,$countj = count($weekStar[$i]['works']);$j<$countj;$j++) {
                $weekStar[$i]['works'][$j]['image'] = D('Content/Document')->getPic($weekStar[$i]['works'][$j]['recordid'], 'thumb');
            }
        }
        return $weekStar;
    }

}