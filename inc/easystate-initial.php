<?php

class EasyStateAPIPlugin
{
    public function plugin_activation()
    {
        $activation = new EasyStateActivation();
        $activation->activate();
    }

    public function plugin_deactivation()
    {
        $deactivation = new EasyStateDeactivation();
        $deactivation->deactivate();
    }
}
