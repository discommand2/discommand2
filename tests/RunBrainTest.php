<?php

use PHPUnit\Framework\TestCase;

class RunBrainTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testRunBrainWithoutArguments()
    {
        // Run the script without arguments
        exec('php scripts/runBrain.php', $output, $return_var);

        // Check the return code
        $this->assertNotEquals(0, $return_var);

        // Check the output
        $this->assertStringContainsString('Please provide the name of the brain to run.', implode("\n", $output));
    }

    /**
     * @coversNothing
     */
    public function testRunBrainWithArgument()
    {
        // Run the script with a specific argument
        exec('php scripts/runBrain.php testBrain', $output, $return_var);

        // Check the return code
        $this->assertEquals(0, $return_var);

        // Check the output
        $this->assertStringContainsString('testBrain.INFO: testBrain is alive.', implode("\n", $output));
    }
}
