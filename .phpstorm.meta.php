<?php
	namespace PHPSTORM_META {
	/** @noinspection PhpUnusedLocalVariableInspection */
	/** @noinspection PhpIllegalArrayKeyTypeInspection */
	$STATIC_METHOD_TYPES = [

		\D('') => [
			'Mongo' instanceof Think\Model\MongoModel,
			'GuestView' instanceof Model\GuestViewModel,
			'View' instanceof Think\Model\ViewModel,
			'AdminNav' instanceof Common\Model\AdminNavModel,
			'OauthUser' instanceof Common\Model\OauthUserModel,
			'AuthRule' instanceof Common\Model\AuthRuleModel,
			'Base' instanceof Common\Model\BaseModel,
			'Album' instanceof Model\AlbumModel,
			'AuthGroupAccess' instanceof Common\Model\AuthGroupAccessModel,
			'ShoppingCart' instanceof Common\Model\ShoppingCartModel,
			'Adv' instanceof Think\Model\AdvModel,
			'ArticleView' instanceof Model\ArticleViewModel,
			'Relation' instanceof Think\Model\RelationModel,
			'User' instanceof Model\UserModel,
			'ReplyView' instanceof Model\ReplyViewModel,
			'Admin' instanceof Common\Model\AdminModel,
			'UserView' instanceof Model\UserViewModel,
			'Merge' instanceof Think\Model\MergeModel,
			'CommentView' instanceof Model\CommentViewModel,
			'KeepView' instanceof Model\KeepViewModel,
			'LetterView' instanceof Model\LetterViewModel,
			'AuthGroup' instanceof Common\Model\AuthGroupModel,
		],
	];
}