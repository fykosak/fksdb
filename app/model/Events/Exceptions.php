<?php

namespace Events;

use RuntimeException;

class MachineExecutionException extends RuntimeException {
    
}

class TransitionConditionFailedException extends MachineExecutionException {
    
}

class SubmitProcessingException extends RuntimeException {
    
}

class TransitionOnExecutedException extends MachineExecutionException {
    
}