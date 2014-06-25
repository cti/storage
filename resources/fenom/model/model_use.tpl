{foreach $model->getUsageList($schema) as $usage}
{var $usage = $schema->getModel($usage)}
use {$usage->getModelClass()} as {$usage->getClassName()};
{/foreach}
