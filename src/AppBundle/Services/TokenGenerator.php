<?php

namespace AppBundle\Services;


class TokenGenerator
{
    public function generate() {
        return sha1(md5(time()));
    }
}