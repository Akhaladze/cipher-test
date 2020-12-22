<?php

namespace frontend\models;

/**
 * This is the ActiveQuery class for [[Fakedata]].
 *
 * @see Fakedata
 */
class FakedataQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Fakedata[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Fakedata|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
