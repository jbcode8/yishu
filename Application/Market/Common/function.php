<?php
/*
 * 本模块下公用方法
 * author KAIWEI SUN 663642331@qq.com
 */
//取图片（艺术家<1> / 作品<多>）
function getPic($id, $type, $limit) {
    if ($limit == 1 && $type == 'thumb') {
        return D('ArtistView')->field('savepath,savename')->where(array('recordid' => $id, 'type' => 'thumb'))->limit($limit)->find();
    } else {
        return D('ArtistView')->field('savepath,savename')->where(array('recordid' => $id, 'type' => 'image'))->limit($limit)->select();
    }
}

//重组数组
function getData($data_arr) {
    foreach ($data_arr as $v) {
        $recordid['recordid'] = getPic($v['recordid'], 'thumb', 1);
        if ($v['artist_works']) {
            foreach ($v['artist_works'] as $c) {
                $child['pic'] = getPic($c['recordid'], 'image', 10);
                $artist_works['artist_works'][] = array_merge($c, $child);
                unset($child['pic']);
            }
        }
        $data[] = array_merge($v, $recordid, $artist_works);
        unset($artist_works['artist_works']);
    }
    return $data;
}
