<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
abstract class MoovicoPluginInterface
{
    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function onStart()
    {
        return true;
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function onRoute()
    {
        return true;
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function onController()
    {
        return true;
    }

    /**
     * TODO: short description.
     * 
     * @param MoovicoController $controller 
     * 
     * @return TODO
     */
    public function onAction(MoovicoController $controller)
    {
        return true;
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function onResponse()
    {
        return true;
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function onFinish()
    {
        return true;
    }
}
