<?php

namespace Sassnowski\Pipeline;


use InvalidArgumentException;
use RuntimeException;

class Pipeline
{
    /**
     * @var array
     */
    protected $steps;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * Pipeline constructor.
     *
     * @param $value The value that should be piped through all steps.
     * @param array $steps
     */
    private function __construct($value = null, $steps = [])
    {
        $this->value = $value;
        $this->steps = $steps;
    }

    /**
     * Factory function to create a new pipeline with the provided initial value.
     * 
     * @param $init
     * @return static
     */
    static function pipe($init)
    {
        return new static($init);
    }

    /**
     * Creates a new pipeline without an initial value.
     * 
     * @return static
     */
    static function build()
    {
        return new static();
    }

    /**
     * Add a step to the pipeline.
     * 
     * @param $next The function that should be executed for this step.
     * @return $this
     */
    function through($next)
    {
        if (! is_callable($next))
        {
            throw new InvalidArgumentException("Unable to add step. All steps have to be callable.");
        }
        
        $newSteps = $this->steps;
        $newSteps[] = $next;
        
        return new static($this->value, $newSteps);
    }

    /**
     * Process the pipeline.
     *
     * @param null $value
     * @return mixed
     */
    function run($value = null)
    {
        $initial = $this->getValue($value);
        
        return array_reduce($this->steps, function ($acc, $next)
        {
            return $next($acc);
        }, $initial);
    }

    /**
     * Alias for `through`.
     * 
     * @param $step
     * @return Pipeline
     */
    function firstThrough($step)
    {
        return $this->through($step);
    }

    /**
     * Alias for `through`.
     * 
     * @param $step
     * @return Pipeline
     */
    function andThen($step)
    {
        return $this->through($step);
    }
    
    /**
     * Determines the value that should be piped through the pipeline.
     *
     * @param $external 
     * @return mixed
     */
    protected function getValue($external)
    {
        $value = ! is_null($external) ? $external : $this->value;

        if (is_null($value))
        {
            throw new RuntimeException(
                "Pipeline has no value set. You can provide a value by passing it to the run() method."
            );
        }
        
        return $value;
    }
}

