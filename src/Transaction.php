<?php

namespace AmirHossein5\LaravelImage;

use AmirHossein5\LaravelImage\Facades\Image;

trait Transaction
{
    /**
     * Whether on transaction or not.
     * 
     * @var bool
     */
    protected bool $transactioning = false;

    /**
     * Array of saved images.
     * 
     * @var array
     */
    protected array $transactionBag = [];

    /**
     * Transaction function.
     * 
     * @param \Closure $closure
     * @param int $attempts = 1
     * 
     * @return void
     * 
     * @throws \Throwable
     */
    public function transaction(\Closure $callback, int $maxAttempts = 1): void
    {
        if ($maxAttempts <= 0) {
            throw new \LogicException('max attempts should be more than 0');
        } 

        for ($currentAttempt = 1; $currentAttempt <= $maxAttempts; $currentAttempt++) {
            $this->beginTransaction();

            // We'll simply execute the given callback within a try / catch block and if we
            // catch any exception we can rollback this transaction so that none of this
            // gets actually persisted to a database or stored in a permanent fashion.
            try {
                $callback();
                $this->commit();
            }

            // If we catch an exception we'll rollback this transaction and try again if we
            // are not out of attempts. If we are out of attempts we will just throw the
            // exception back out and let the developer handle an uncaught exceptions.
            catch (\Throwable $e) {
                $this->handleTransactionException(
                    $e,
                    $currentAttempt,
                    $maxAttempts
                );
                
                continue;
            }
        }
    }

    /**
     * Starts the transaction.
     * 
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->transactioning = true;
    }

    /**
     * Commits the transaction.
     * 
     * @return void
     */
    public function commit(): void
    {
        if ($this->transactioning === false) {
            return;
        }
        
        foreach ($this->transactionBag as $transactioned) {
            $this->disk($transactioned['disk']);
            $this->mkdirIfNotExists($transactioned['imageDirectory']);

            if ($transactioned['willBeReplace']) {
                $this->removeIfExists($transactioned['imagePath']);
            }

            $this->store(
                $transactioned['image'],
                $transactioned['sizes'],
                $transactioned['imagePath'],
                $transactioned['upsize'],
                $transactioned['quality'],
            );
        }

        $this->transactioning = false;
        $this->transactionBag = [];
    }

    /**
     * RollBacks the transaction.
     * 
     * @return void
     */
    public function rollBack(): void
    {
        if ($this->transactioning === false) {
            return;
        }

        $this->transactioning = false;
        $this->transactionBag = [];
    }

    /**
     * Handles the exception
     * 
     * @param \Throwable $e
     * @param int $currentAttempts = 1
     * @param int $maxAttempts = 1
     * 
     * @return void
     * 
     * @throws \Throwable
     */
    private function handleTransactionException(\Throwable $e, int $currentAttempt = 1, int $maxAttempts = 1): void
    {
        if ($currentAttempt >= $maxAttempts) {
            $this->rollBack();
            throw $e;
        }
    }
}
