<?php
// +----------------------------------------------------------------------
// | NewsLogic.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Logic;

class NewsLogic extends BaseLogic{

    /**
     * 新增,更新操作
     * @param int $id
     * @return bool
     */
    public function update($id = 0){
        /* 获取文章数据 */
        $data = $this->create();
        if($data === false){
            return false;
        }

        /* 添加或更新数据 */
        if(empty($data['id'])){//新增数据
            $data['id'] = $id;
            $id = $this->add($data);
            if(!$id){
                $this->error = '新增详细内容失败！';
                return false;
            }
        } else { //更新数据
            $status = $this->save($data);
            if(false === $status){
                $this->error = '更新详细内容失败！';
                return false;
            }
        }

        return true;
    }
} 