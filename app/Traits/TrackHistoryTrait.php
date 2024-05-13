<?php

namespace App\Traits;

use App\Models\History;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

trait TrackHistoryTrait
{
    protected function track(Model $model, callable $func = null, $table = null, $id = null, $custom_fields = [], $event = 'update')
    {
        // Allow for overriding of table if it's not the model table
        $table = $table ?: get_class($model);
        // Allow for overriding of id if it's not the model id
        $id = $id ?: $model->id;
        // Allow for customization of the history record if needed
        $func = $func ?: [$this, 'getHistoryBody'];

        // Get the dirty fields and run them through the custom function, then insert them into the history table
        $updated = $this->getUpdated($model)
        ->map(function ($value, $key) use ($func) {
            $field = $value['field'];
            $newValue = $value['new_value'];
            $oldValue = $value['old_value'];

            return [
                'field' => $field,
                'new_value' => $newValue,
                'old_value' => $oldValue,
                ...call_user_func_array($func, [$newValue, $oldValue, $field]),
            ];
        });

        if(count($custom_fields) > 0) {
            $updated->push(...$custom_fields);
        }

        History::create([
            'trackable_type' => $table,
            'trackable_id'   => $id,
            'actor_id'       => Auth::id(),
            'fields' => $updated->toArray(),
            'event' => $event
        ]);
    }

    protected function getHistoryBody($newValue, $oldValue, $field)
    {
        return [
            // 'body' => "Updated '" . $field . "' from '" . $oldValue . "' to '" . $newValue . "'",
        ];
    }

    protected function getUpdated($model)
    {
        return collect($model->getDirty())->filter(function ($value, $key) {
            // We don't care if timestamps are dirty, we're not tracking those
            return !in_array($key, ['created_at', 'updated_at']);
        })->map(function ($value, $key) use ($model) {
            // Take the field names and convert them into human readable strings for the description of the action
            // e.g. first_name -> first name
            $oldValue = $model->getOriginal($key);
            return [
                'field' => $key,
                'new_value' => $value,
                'old_value' => $oldValue,
            ];
        })->values();
    }
}
