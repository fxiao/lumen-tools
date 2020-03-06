<?php
/**
 * @file Controller.php
 * @brief Controller 基础类
 */
namespace Fxiao\LumenTools;

use Laravel\Lumen\Routing\Controller as BaseController;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Exception\ValidationHttpException;

/**
 * @brief Controller 基础类
 */
class Controller extends BaseController
{
    /**
     * @brief 接口帮助调用
     */
    use Helpers;

    /**
     * @brief 返回错误的请求
     */
    protected function errorBadRequest($validator)
    {
        // github like error messages
        // if you don't like this you can use code bellow
        //
        //throw new ValidationHttpException($validator->errors());

        $result = [];
        $messages = $validator->errors()->toArray();

        if ($messages) {
            foreach ($messages as $field => $errors) {
                foreach ($errors as $error) {
                    $result[] = [
                        'field' => $field,
                        'code' => $error,
                    ];
                }
            }
        }

        throw new ValidationHttpException($result);
    }
}
