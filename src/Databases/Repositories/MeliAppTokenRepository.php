<?php

namespace WebDEV\Meli\Databases\Repositories;

use Illuminate\Database\Eloquent\Model;
use WebDEV\Meli\Databases\Models\MeliAppToken;
use Exception;
use Illuminate\Support\Facades\DB;

class MeliAppTokenRepository
{
    /**
     * @param string $state
     * @return Model
     */
    public function find(string $state): Model {
        $token = MeliAppToken::query()->where('state', '=', $state)->first();
        if(!$token) {
            $token = new MeliAppToken();
        }
        return $token;
    }

    /**
     * @param array $params
     * @return Model
     * @throws Exception
     */
    public function create(array $params): Model {
        DB::beginTransaction();
        try {
            $token = new MeliAppToken($params);
            $token->save();
            DB::commit();
            return $token;
        } catch(Exception $ex) {
            DB::rollBack();
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * @param string $state
     * @param array $params
     * @return Model
     * @throws Exception
     */
    public function update(string $state, array $params): Model {
        DB::beginTransaction();
        try {
            $token = MeliAppToken::query()->where('state', '=', $state)->firstOrFail();
            $token->update($params);
            DB::commit();
            return $token;
        } catch(Exception $ex) {
            DB::rollBack();
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * @param string $state
     * @throws Exception
     */
    public function delete(string $state): void {
        DB::beginTransaction();
        try {
            MeliAppToken::query()->where('state', '=', $state)->delete();
            DB::commit();
        } catch(Exception $ex) {
            DB::rollBack();
            throw new Exception($ex->getMessage());
        }
    }
}
