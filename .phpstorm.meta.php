<?php
	namespace PHPSTORM_META {
	/** @noinspection PhpUnusedLocalVariableInspection */
	/** @noinspection PhpIllegalArrayKeyTypeInspection */
	$STATIC_METHOD_TYPES = [

		\D('') => [
			'Adv' instanceof Think\Model\AdvModel,
			'Mongo' instanceof Think\Model\MongoModel,
			'GuestView' instanceof Model\GuestViewModel,
			'View' instanceof Think\Model\ViewModel,
			'Relation' instanceof Think\Model\RelationModel,
			'User' instanceof Model\UserModel,
			'ReplyView' instanceof Model\ReplyViewModel,
			'Album' instanceof Model\AlbumModel,
			'Merge' instanceof Think\Model\MergeModel,
		],
	];
}