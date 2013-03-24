<?php
namespace Arsenal\Database;

/*
    Instantiable class for a mapped object. Even without any logic here,
    this separation is mandatory so the object's properties won't collide 
    with the private ones in the abstract class.
*/
class MappedObject extends MappedObjectAbstract
{
    // all inherited
}