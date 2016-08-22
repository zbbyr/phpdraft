<?php
/**
 * This file contains the RequestBodyElement
 *
 * @package PHPDraft\Model
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Model;


class RequestBodyElement extends DataStructureElement
{
    /**
     * Parse a JSON object to a data structure
     *
     * @param \stdClass $object       An object to parse
     * @param array     $dependencies Dependencies of this object
     *
     * @return DataStructureElement self reference
     */
    public function parse($object, &$dependencies)
    {
        if (empty($object) || !isset($object->content))
        {
            return $this;
        }
        $this->element = $object->element;
        if (isset($object->content) && is_array($object->content))
        {
            foreach ($object->content as $value)
            {
                $struct        = new RequestBodyElement();
                $this->value[] = $struct->parse($value, $dependencies);
            }

            return $this;
        }

        $this->key         = $object->content->key->content;
        $this->type        = $object->content->value->element;
        $this->description = isset($object->meta->description) ? $object->meta->description : NULL;
        $this->status      =
            isset($object->attributes->typeAttributes[0]) ? $object->attributes->typeAttributes[0] : NULL;

        if (!in_array($this->type, $this->defaults))
        {
            $dependencies[] = $this->type;
        }

        if ($this->type === 'object')
        {
            $value       = isset($object->content->value->content) ? $object->content->value : NULL;
            $this->value = new RequestBodyElement();
            $this->value = $this->value->parse($value, $dependencies);

            return $this;
        }

        $this->value = isset($object->content->value->content) ? $object->content->value->content : NULL;

        return $this;
    }

    public function print_request($type = 'application/x-www-form-urlencoded')
    {
        if (is_array($this->value))
        {
            $return = '<code>';
            foreach ($this->value as $object)
            {
                if (get_class($object) === self::class)
                {
                    $return .= $object->print_request($type);
                }
            }

            $return .= '</code>';

            return $return;
        }

        $value = (empty($this->value)) ? '?' : $this->value;

        switch ($type)
        {
            case 'application/x-www-form-urlencoded':
                return $this->key . "=<kbd>" . $value . '</kbd>';
                break;
            default:
                $object             = [];
                $object[$this->key] = $value;

                return json_encode($object) . PHP_EOL;
                break;
        }
    }
}