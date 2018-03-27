<?php

namespace WonderWp\Plugin\Generator\Service;

use WonderWp\Component\HttpFoundation\Result;

class GeneratorService
{
    /** @var string[] */
    protected $data;

    /**
     * @return string[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string[] $data
     *
     * @return static
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function generate()
    {
        $check = $this->checkDatas();
        if ($check->getCode() === 200) {
            $msg = "Starting Generation of the ".$this->data['name']." plugin with the following values : ";
            foreach($this->data as $key=>$val){
                $msg.="\n[$key] => $val";
            }
            \WP_CLI::success($msg);
        } else {
            \WP_CLI::error(implode("\n", $check->getData('errors')), true);
        }
    }

    protected function checkDatas()
    {
        $requiredDatas = ['name', 'desc', 'namespace'];
        $errors        = [];
        foreach ($requiredDatas as $req) {
            if (empty($this->data[$req])) {
                $errors[$req] = 'Attribute ' . $req . ' is missing';
            }
        }
        $code = empty($errors) ? 200 : 403;

        return new Result($code, ['datas' => $this->data, 'errors' => $errors]);
    }

}
