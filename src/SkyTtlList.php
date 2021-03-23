<?php
declare(strict_types=1);

namespace SkyDiablo\SkyTtlList;

use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

class SkyTtlList extends \ArrayIterator
{

    protected LoopInterface $loop;
    protected array $timerList;
    protected float $defaultCleanupInterval;

    /**
     * SkyTtlList constructor.
     * @param LoopInterface $loop
     * @param float $defaultCleanupInterval
     */
    public function __construct(LoopInterface $loop, float $defaultCleanupInterval = 1.0)
    {
        parent::__construct();
        $this->loop = $loop;
        $this->defaultCleanupInterval = $defaultCleanupInterval;
    }

    protected function defineTtlTimer($key, float $ttl = null): void
    {
        $this->unsetTtlTimer($key);
        $this->timerList[$key] = $this->loop->addTimer($ttl ?? $this->defaultCleanupInterval, function () use ($key) {
            unset($this[$key]);
        });
    }

    public function offsetUnset($key)
    {
        $this->unsetTtlTimer($key);
        parent::offsetUnset($key);
    }

    protected function unsetTtlTimer(string $key)
    {
        /** @var TimerInterface $timer */
        if ($timer = $this->timerList[$key] ?? null) {
            $this->loop->cancelTimer($timer);
        }
        unset($this->timerList[$key]);
    }


    /**
     * alias for "offsetSet"
     * @param string $key
     * @param mixed $value
     * @param float|null $ttl
     * @return SkyTtlList
     */
    public function set(string $key, $value, float $ttl = null): SkyTtlList
    {
        $this->offsetSet($key, $value, $ttl);
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this[$key];
    }

    /**
     * @param string $key
     * @param string $value
     * @param float|null $ttl
     */
    public function offsetSet($key, $value, float $ttl = null)
    {
        $this->defineTtlTimer($key, $ttl);
        parent::offsetSet($key, $value);
    }

}