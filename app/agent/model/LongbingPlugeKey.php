<?PHP

namespace app\agent\model;
use app\BaseModel;

Class LongbingPlugeKey extends BaseModel
{
    protected $name = 'lb_pluge_key';
    
    public function getPlugeKey($filter = [])
    {
        $result = $this;
        if(!empty($filter)) $result = $result->where($filter);
        $result = $result->limit(1)->select();
        if(!empty($result)) 
        {
            $result = $result->toArray();
            if(isset($result[0])) $result = $result[0];
        }
        return $result;
    }
    
    public function updatePlugeKey($filter ,$data = [])
    {
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
        return !empty($result);
    }
    
}
