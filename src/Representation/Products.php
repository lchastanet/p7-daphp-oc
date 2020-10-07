<?php

namespace App\Representation;

use Pagerfanta\Pagerfanta;
use JMS\Serializer\Annotation\Type;

class Products
{
    /**
     * @Type("array<App\Entity\Product>")
     */
    public $data;

    public $meta;

    public function __construct(Pagerfanta $data)
    {
        $this->data = $data->getCurrentPageResults();
        $this->addMeta('limit', $data->getMaxPerPage());
        $this->addMeta('current_items', count($data->getCurrentPageResults()));
        $this->addMeta('total_items', $data->getNbResults());
        $this->addMeta('offset', $data->getCurrentPageOffsetStart());
    }

    public function addMeta($name, $value)
    {
        if (isset($this->meta[$name])) {
            throw new \LogicException(sprintf('This meta already exists. You are trying to override this meta, use the setMeta method instead for the %s meta.', $name));
        }

        $this->setMeta($name, $value);
    }

    public function setMeta($name, $value)
    {
        $this->meta[$name] = $value;
    }
}
