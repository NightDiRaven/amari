<?php

namespace Amari\Contracts;

interface WithJsonImageContract
{
    public function withJsonImageOnCreating();
    public function withJsonImageOnCreated();
    public function withJsonImageOnUpdating();
    public function withJsonImageOnUpdated();
    public function withJsonImageOnDeleted();
}