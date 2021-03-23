<?php
declare(strict_types=1);

namespace SkyDiablo\SkyTtlList;

use React\EventLoop\LoopInterface;

class SkyTtlList extends \ArrayIterator
{

    protected LoopInterface $loop;
    protected array $list;
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

    protected function defineTtlTimer(float $ttl, string $key)
    {
        $this->loop->addTimer($ttl ?? $this->defaultCleanupInterval, function () use ($key) {
            unset($this[$key]);
        });
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
        return $this->list[$key];
    }

    /**
     * @param string $key
     * @param string $value
     * @param float|null $ttl
     */
    public function offsetSet($key, $value, float $ttl = null)
    {
        $this->defineTtlTimer($ttl, $key);
        parent::offsetSet($key, $value);
    }

}