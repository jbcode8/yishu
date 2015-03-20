<?php
/**
 * Description of ArtistViewModel(视图模型)
 * @date 2014/08/07
 * @author KAIWEI SUN <663642331@qq.com>
 */

namespace Market\Model;

use Think\Model\ViewModel;

class ArtistViewModel extends ViewModel {

    protected $viewFields = array(
        'record' => array(
            'recordid', 'sourceid', 'create_time',
            '_type' => 'LEFT'
        ),
        'attachment' => array(
            'savepath', '_id', 'savename', 'type', '_on' => 'attachment._id = record.sourceid'
        )
    );

}
