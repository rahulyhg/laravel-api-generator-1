<?php

namespace Cheppers\LaravelApiGenerator\Generators;

use Carbon\Carbon;
use Cheppers\LaravelApiGenerator\Generators\Config\ConfigStore;
use Faker\Generator;

class TestOrderGenerator extends GeneratorAbstract
{
    private $fieldsWithValue = [];

    public function __construct(ConfigStore $config, $stubDirectory, $destinationDirectory, Generator $faker)
    {
        parent::__construct($config, $stubDirectory, $destinationDirectory, $faker);
        foreach ($this->fields as $delta => $fieldData) {
            if ($fieldData['type'] == 'text') {
                continue;
            }
            $this->fieldsWithValue[$delta] = $this->generateValues($fieldData);
        }
        if ($this->timestamps) {
            $fieldData = [
                'name' => 'created_at',
                'type' => 'datetime',
            ];
            $this->fieldsWithValue[] = $this->generateValues($fieldData);
            $fieldData = [
                'name' => 'updated_at',
                'type' => 'datetime',
            ];
            $this->fieldsWithValue[] = $this->generateValues($fieldData);
        }
    }

    protected function getStubFileName()
    {
        return 'TestOrder.php.txt';
    }

    protected function getDestinationFileName()
    {
        return $this->modelName . 'OrderTest.php';
    }

    protected function extendReplaceData()
    {
        $code = '';
        foreach ($this->fieldsWithValue as $fieldData) {
            if ($fieldData['type'] == 'text') {
                continue;
            }
            $code .= $this->maketestFunction($fieldData);
        }
        $this->stringsToReplace['%%code%%'] = rtrim($code);
    }

    private function maketestFunction($fieldData)
    {
        $code = $this->indentString("public function testOrderBy" . studly_case($fieldData['name']) . "()", 1);
        $code .= $this->indentString("{", 1);
        $code .= $this->indentString("factory(" . studly_case($this->modelName) . "::class)->create([", 2);
        $code .= $this->indentString("'" . $fieldData['name'] . "' => '" . $fieldData['first value'] . "',", 3);
        $code .= $this->indentString("]);", 2);
        $code .= $this->indentString("factory(" . studly_case($this->modelName) . "::class)->create([", 2);
        $code .= $this->indentString("'" . $fieldData['name'] . "' => '" . $fieldData['second value'] . "',", 3);
        $code .= $this->indentString("]);", 2);
        $code .= $this->indentString(
            "\$response = \$this->getJsonRequest('api/" . snake_case($this->modelName) .
            "?orderby=" . $fieldData['name'] . "&sortorder=asc');", 2
        );
        $code .= $this->indentString("\$response->assertStatus(Response::HTTP_OK);", 2);
        $code .= $this->indentString("\$responseData = \$response->decodeResponseJson();", 2);
        $code .= $this->indentString("\$this->assertCount(2, \$responseData['data']);", 2);
        $code .= $this->indentString("\$this->assertEquals('" . $fieldData['first value'] . "', \$responseData['data'][0]['attributes']['" . $fieldData['name'] . "']);", 2);
        $code .= $this->indentString("\$this->assertEquals('" . $fieldData['second value'] . "', \$responseData['data'][1]['attributes']['" . $fieldData['name'] . "']);", 2);
        $code .= $this->indentString("", 0);
        $code .= $this->indentString(
            "\$response = \$this->getJsonRequest('api/" . snake_case($this->modelName) .
            "?orderby=" . $fieldData['name'] . "&sortorder=desc');", 2
        );
        $code .= $this->indentString("\$response->assertStatus(Response::HTTP_OK);", 2);
        $code .= $this->indentString("\$responseData = \$response->decodeResponseJson();", 2);
        $code .= $this->indentString("\$this->assertCount(2, \$responseData['data']);", 2);
        $code .= $this->indentString("\$this->assertEquals('" . $fieldData['second value'] . "', \$responseData['data'][0]['attributes']['" . $fieldData['name'] . "']);", 2);
        $code .= $this->indentString("\$this->assertEquals('" . $fieldData['first value'] . "', \$responseData['data'][1]['attributes']['" . $fieldData['name'] . "']);", 2);
        $code .= $this->indentString("}", 1);
        $code .= $this->indentString("", 0);
        return $code;
    }
    private function generateValues($fieldData)
    {
        switch ($fieldData['type']) {
            case 'integer':
                $fieldData['first value'] = $this->faker->numberBetween(1, 10000);
                $fieldData['second value'] = $fieldData['first value'] + 2;
                break;
            case 'string':
                $fieldData['first value'] = 'first sample text';
                $fieldData['second value'] = 'second sample text';
                break;
            case 'boolean':
                $fieldData['first value'] = 0;
                $fieldData['second value'] = 1;
                break;
            case 'datetime':
                $fieldData['first value'] = Carbon::now();
                $fieldData['second value'] = Carbon::now()->addDay();
                break;
            default:
                throw $this->invalidFieldTypeException($fieldData['type']);
                break;
        }
        return $fieldData;
    }
}
