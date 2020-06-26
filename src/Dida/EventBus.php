<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida;

use \Dida\Event\EventException;

/**
 * EventBus 事件总线
 */
class EventBus
{
    /**
     * 版本号
     */
    const VERSION = '20200627';

    /**
     * 错误代码
     */
    const ERROR_EVENT_NOT_FOUND = 1000;

    /*
     * 所有已经登记的事件
     * $events["event_name"] = true
     *
     * @var array
     */
    protected static $events = [];

    /*
     * 所有已经登记的事件回调函数
     * $hooks["event_name"][id] = [$callback, $parameters]
     */
    protected static $hooks = [];

    /**
     * 新增一个event。
     *
     * @param string $event 事件名称
     *
     * @return void
     */
    public static function addEvent($event)
    {
        // 如果事件不存在，则创建之
        if (!isset(self::$events[$event])) {
            self::$events[$event] = true;
        }
    }

    /**
     * 删除一个用户事件，以及所有已挂接到这个事件上的回调函数。
     *
     * @param string $event 事件名称
     *
     * @return bool 删除事件是否完成。
     */
    public static function removeEvent($event)
    {
        unset(self::$events[$event], self::$hooks[$event]);
    }

    /**
     * 检查一个事件是否已经存在。
     *
     * @param string $event 事件名称
     *
     * @return bool
     */
    public static function hasEvent($event)
    {
        return isset(self::$events[$event]);
    }

    /**
     * 绑定一个回调函数到指定事件上。
     *
     * @param string   $event      事件名称
     * @param callback $callback   回调函数
     * @param array    $parameters 回调函数的参数
     * @param string   $id         指定回调函数的id
     *
     * @return int 成功返回0,失败返回错误代码.
     */
    public static function hook($event, $callback, array $parameters = [], $id = null)
    {
        if (self::hasEvent($event)) {
            // 如果不指定id, 则callback放到队列尾部
            // 如果指定了id, 则用新的callback代替旧的callback
            if ($id === null) {
                self::$hooks[$event][] = [$callback, $parameters];
            } else {
                self::$hooks[$event][$id] = [$callback, $parameters];
            }

            // 成功
            return 0;
        } else {
            return EventBus::ERROR_EVENT_NOT_FOUND;
        }
    }

    /**
     * 解除某个事件上挂接的某个或者全部回调函数。
     * 如果不指定id，则表示解除此事件上的所有回调函数。
     *
     * @param string $event 事件名称
     * @param string $id    回调函数的id
     */
    public static function unhook($event, $id = null)
    {
        // 如果不指定id, 则删除这个event的所有callbacks
        // 如果指定了id, 则只删除event的指定callback
        if ($id === null) {
            unset(self::$hooks[$event]);
        } else {
            unset(self::$hooks[$event][$id]);
        }
    }

    /**
     * 触发一个事件，并执行挂接在这个事件上的所有回调函数。
     * 注：如果某个回调函数返回false，则不再执行后面的回调函数。
     *
     * @param string $event       事件名称
     * @param bool   $ignoreError 如果callback出错,是否继续执行后续的其它callbacks
     *
     * @return bool 是否成功执行了event的所有hooks
     */
    public static function trigger($event, $ignoreError = false)
    {
        if (array_key_exists($event, self::$hooks)) {
            /*
             * 依次执行此事件上的所有回调函数.
             * 如果某个回调函数返回false，则不再执行后面的回调函数。
             */
            foreach ($hooks as $hook) {
                list($callback, $parameters) = $hook;

                if ($ignoreError) {
                    call_user_func_array($callback, $parameters);
                } else {
                    // 如果执行结果是false, 不执行后续callback, 并返回失败
                    if (call_user_func_array($callback, $parameters) === false) {
                        return false;
                    }
                }
            }
        }

        // 返回成功
        return true;
    }
}
