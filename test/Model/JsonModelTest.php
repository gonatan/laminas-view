<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\View\Model;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class JsonModelTest extends TestCase
{
    public function testAllowsEmptyConstructor()
    {
        $model = new JsonModel();
        $this->assertInstanceOf('Zend\View\Variables', $model->getVariables());
        $this->assertEquals([], $model->getOptions());
    }

    public function testCanSerializeVariablesToJson()
    {
        $array = ['foo' => 'bar'];
        $model = new JsonModel($array);
        $this->assertEquals($array, $model->getVariables());
        $this->assertEquals(Json::encode($array), $model->serialize());
    }

    public function testCanSerializeWithJsonpCallback()
    {
        $array = ['foo' => 'bar'];
        $model = new JsonModel($array);
        $model->setJsonpCallback('callback');
        $this->assertEquals('callback(' . Json::encode($array) . ');', $model->serialize());
    }

    public function testPrettyPrint()
    {
        $array = [
            'simple'=>'simple test string',
            'stringwithjsonchars'=>'\"[1,2]',
            'complex'=>[
                'foo'=>'bar',
                'far'=>'boo'
            ]
        ];
        $model = new JsonModel($array, ['prettyPrint' => true]);
        $this->assertEquals(Json::encode($array, false, ['prettyPrint' => true]), $model->serialize());
    }
}
