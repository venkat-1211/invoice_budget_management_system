<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*'], array $relations = [])
    {
        return DB::table($this->model->getTable())
            ->select($columns)
            ->whereNull('deleted_at')
            ->get();
    }

    public function find(int $id, array $columns = ['*'], array $relations = [])
    {
        return DB::table($this->model->getTable())
            ->select($columns)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();
    }

    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = [])
    {
        return DB::table($this->model->getTable())
            ->select($columns)
            ->where('uuid', $uuid)
            ->whereNull('deleted_at')
            ->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $record = $this->model->find($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $record = $this->model->find($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }

    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return DB::table($this->model->getTable())
            ->select($columns)
            ->whereNull('deleted_at')
            ->paginate($perPage);
    }
}
