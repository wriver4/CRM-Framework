<?php
use Whoops\Handler\Handler;
use Psr\Log\LoggerInterface;

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

/**
 * A custom Whoops handler to log exceptions using a PSR-3 compatible logger (like Monolog).
 */
class Logit extends Handler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger A PSR-3 compatible logger instance.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $exception = $this->getException();
        $this->logger->error($exception->getMessage(), [
            'exception' => $exception,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        return Handler::LAST_HANDLER; // Allow other Whoops handlers to run after logging
    }
}