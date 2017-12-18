<?php

class Upload{

    const ERROR_CODE   = 500;     //错误码
    const SUCCESS_CODE = 200;   //成功码

    private $filepath = './uploads/'; //上传目录
    private $chunkNum;                //第几个文件块
    private $totalChunkNum;           //文件块总数
    private $fileName;                //文件名

    public function __construct()
    {

    }

    public function run(){

        $act = trim( isset($_REQUEST['act'])?$_REQUEST['act']:'' );
        if ($act == 'upload') {
            $rdata = $this->uploadChunk($_POST['chunkNum'],$_POST['fileName']);
        }else if($act == 'merge'){
            $rdata = $this->mergeFile($_POST['totalChunkNum'],$_POST['fileName']);
        }else{
           $this->sendResponse(self::ERROR_CODE,'你（bie）好（xia）可（qing）爱（qiu）');
        }
    }

    /**
     * 上传块文件
     * @param $chunkNum
     * @param $fileName
     */
    private function uploadChunk($chunkNum,$fileName){

        $target = $this->filepath . iconv("utf-8","gbk",$fileName) . '-' . $chunkNum;
        $move_result = move_uploaded_file($_FILES['file']['tmp_name'], $target);
        if(!$move_result){
            $this->sendResponse(self::ERROR_CODE, '上传块文件失败');
        }

        // Might execute too quickly.
        //sleep(1);

        $this->sendResponse(self::SUCCESS_CODE,'上传块文件成功');
    }

    /**
     * 合并块文件
     * @param $totalChunkNum
     * @param $fileName
     */
    private function mergeFile($totalChunkNum,$fileName){
        //文件合并
        $target = $this->filepath . iconv("utf-8","gbk",$fileName);
        $dst = fopen($target, 'wb');

        for($i = 0; $i < $totalChunkNum; $i++) {
            $slice = $target . '-' . $i;
            $src = fopen($slice, 'rb');
            stream_copy_to_stream($src, $dst);
            fclose($src);
            unlink($slice);
        }

        fclose($dst);

        $this->sendResponse(self::SUCCESS_CODE,'合并块文件成功',[
            'url'   =>  'www.***.com/uploads/abc.zip'
        ]);
    }

    private function sendResponse($code,$msg,$data = []){

        header('Content-Type: application/json; charset=utf-8', true);
        echo json_encode([
            'code'  =>  $code,
            'msg'   =>  $msg,
            'data'  =>  $data,
        ]);exit;

    }

}

$obj = new Upload();
$obj->run();

