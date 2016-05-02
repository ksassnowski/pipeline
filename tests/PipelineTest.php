<?php

use Sassnowski\Pipeline\Pipeline;

class PipelineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider pipelineProvider
     */
    function it_applies_the_all_registered_steps_to_an_initial_value($value, $expected)
    {
        $result = Pipeline::pipe($value)->through(function ($i) {
            return $i + 1;
        })->through(function ($i) {
            return $i * 10;
        })->run();
        
        $this->assertEquals($expected, $result);
    }
    
    function pipelineProvider()
    {
        return [
            [0, 10],
            [10, 110],
            [-3, -20],
        ];
    }

    /** @test */
    function it_requires_all_steps_to_be_callable()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        
        Pipeline::pipe(10)->through(10)->run();
    }

    /**
     * @dataProvider invokeProvider
     * @test
     */
    function it_accepts_a_class_that_implements_the_invoke_magic_method($value, $expected)
    {
        $actual = Pipeline::pipe($value)->through(new Add10)->run();
        
        $this->assertEquals($expected, $actual); 
    }
    
    function invokeProvider()
    {
        return [
            [0, 10],
            [10, 20],
            [-5, 5],
        ];
    }
    
    /** @test */
    function it_accepts_a_callable_array_as_a_step()
    {
        $actual = Pipeline::pipe(10)->through([$this, 'add10'])->run();
        
        $this->assertEquals(20, $actual);
    }

    /** @test */
    function it_returns_a_new_pipeline_for_each_added_step()
    {
        $pipeline1 = Pipeline::pipe(10)->through(function ($i) { return $i + 1; });
        $pipeline2 = $pipeline1->through(function ($i) { return $i * 10; });
        
        $actual1 = $pipeline1->run();
        $actual2 = $pipeline2->run();
        
        $this->assertEquals(11, $actual1);
        $this->assertEquals(110, $actual2);
    }
    
    /** 
     * @test 
     * @dataProvider builderProvider
     */
    function it_allows_building_a_pipeline_without_seeding_it_with_a_value($value, $expected)
    {
        $pipeline = Pipeline::build()->through(function ($i) { return $i + 1; });
        
        $actual = $pipeline->run($value);
        
        $this->assertEquals($expected, $actual);
    }
    
    function builderProvider()
    {
        return [
            [0, 1],
            [-1, 0],
            [1, 2],
        ];
    }

    /** @test */
    function it_should_throw_an_exception_if_no_value_is_provided()
    {
        $this->setExpectedException(RuntimeException::class);

        Pipeline::build()->through(function ($i) { return $i; })->run();
    }
    
    function add10($i)
    {
        return $i + 10;
    }
    
    /** @test */
    function it_provides_aliases_for_chaining_steps()
    {
        $actual = Pipeline::pipe(10)
            ->firstThrough(function ($i) { return $i + 10; })
            ->andThen(function ($i) { return $i - 5; })
            ->andThen(function ($i) { return $i % 2; })
            ->run();
        
        $this->assertEquals(1, $actual);
    }
}

class Add10
{
    function __invoke($value)
    {
        return $value + 10;
    }
}
