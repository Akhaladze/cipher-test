<?php

namespace frontend\models;

/**
 * This is the ActiveQuery class for [[Testdata]].
 *
 * @see Testdata
 */
class TestdataQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Testdata[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Testdata|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
