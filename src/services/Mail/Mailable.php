<?php

namespace Services\Mail;

abstract class Mailable
{
    abstract public function build(): EmailMessage;
}
