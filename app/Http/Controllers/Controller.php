<?php

namespace App\Http\Controllers;

abstract class Controller extends \Illuminate\Routing\Controller
{
    protected function requireCrudPermissions(string $resource): void
    {
        $this->middleware("permission:{$resource}.view")->only(['index', 'show']);
        $this->middleware("permission:{$resource}.create")->only(['create', 'store']);
        $this->middleware("permission:{$resource}.update")->only(['edit', 'update']);
        $this->middleware("permission:{$resource}.delete")->only('destroy');
    }
}
