<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida;

/**
 * EventBus 事件总线
 */
final class EventBus
{
    /**
     * Version
     */
    const VERSION = '20171124';

    /*
     * 所有已经登记的事件
     * $events["event_name"] = true
     *
     * @var array
     */
    protected $events = [];

    /*
     * 所有已经登记的事件回调函数
     * $hooks["event_name"][id] = [$callback, $parameters]
     */
    protected $hooks = [];

    /**
     * 新增一个event。
     *
     * @param string $event 事件名称
     */
    public function add($event)
    {
        // 如果事件不存在，则创建之
        if (!isset($this->events[$event])) {
            $this->events[$event] = true;
            return true;
        }
    }

    /**
     * 删除一个用户事件，以及所有已挂接到这个事件上的回调函数。
     *
     * @param string $event 事件名称
     *
     * @return bool 删除用户事件是否完成。
     */
    public function remove($event)
    {
        unset($this->events[$event], $this->hooks[$event]);
    }

    /**
     * 检查一个事件是否已经存在。
     *
     * @param string $event 事件名称
     */
    public function has($event)
    {
        return isset($this->events[$event]);
    }

    /**
     * 绑定一个回调函数到指定事件上。
     *
     * @param string   $event      事件名称
     * @param callback $callback   回调函数
     * @param array    $parameters 回调函数的参数
     * @param string   $id         设置这个回调函数的id
     *
     * @return $this
     *
     * @throws EventException
     */
    public function hook($event, $callback, array $parameters = [], $id = null)
    {
        if ($this->has($event)) {
            if ($id === null) {
                $this->hooks[$event][] = [$callback, $parameters];
            } else {
                $this->hooks[$event][$id] = [$callback, $parameters];
            }
        } else {
            throw new EventException($event, EventException::EVENT_NOT_FOUND);
        }

        return $this;
    }

    /**
     * 解除某个事件上挂接的某个或者全部回调函数。
     * 如果不指定id，则表示解除此事件上的所有回调函数。
     *
     * @param string $event 事件名称
     * @param string $id    回调函数的id
     */
    public function unhook($event, $id = null)
    {
        if ($id === null) {
            unset($this->hooks[$event]);
        } else {
            unset($this->hooks[$event][$id]);
        }
        return $this;
    }

    /**
     * 触发一个事件，并执行挂接在这个事件上的所有回调函数。
     * 注：如果某个回调函数返回false，则不再执行后面的回调函数。
     *
     * @param string $event 事件名称
     */
    public function trigger($event)
    {
        if (array_key_exists($event, $this->hooks)) {
            /*
             * 依次执行此事件上的所有回调函数
             * 注：如果某个回调函数返回false，则不再执行后面的回调函数。
             */
            foreach ($hooks as $hook) {
                list($callback, $parameters) = $hook;
                if (call_user_func_array($callback, $parameters) === false) {
                    break;
                }
            }
        }
    }
}
