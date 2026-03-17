<?php

namespace Models;

use Modules\Main\BaseModel;

class LinksModel extends BaseModel
{
	protected string $table = 'links';

	public function findAllByProject(int $id) : array
	{
		$result = $this->findAllBy('project_id', $id);

		if (!is_array($result))
		{
			$result = [$result];
		}

		return $result ?: [];
	}
}