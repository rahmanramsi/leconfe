<?php

namespace App\Actions\User;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class TopicUpdateAction
{
    use AsAction;

    public function handle(Topic $topic, array $data): Topic
    {
        try {
            DB::beginTransaction();

            $topic->update($data);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        return $topic;
    }
}
