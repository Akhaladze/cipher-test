<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Testdata;

/**
 * TestdataSearch represents the model behind the search form of `frontend\models\Testdata`.
 */
class TestdataSearch extends Testdata
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user', 'session_cipher', 'request_string', 'response_string', 'data'], 'safe'],
            [['session_counter', 'id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Testdata::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'session_counter' => $this->session_counter,
            'data' => $this->data,
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'user', $this->user])
            ->andFilterWhere(['like', 'session_cipher', $this->session_cipher])
            ->andFilterWhere(['like', 'request_string', $this->request_string])
            ->andFilterWhere(['like', 'response_string', $this->response_string]);

        return $dataProvider;
    }
}
