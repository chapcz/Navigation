<?php

namespace Kollarovic\Navigation;

use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Nette\Utils\Validators;


/**
 * Class Item
 * @package Kollarovic\Navigation
 * @property string $link
 * @property string $label
 * @property string $icon
 * @property mixed $linkArgs
 * @property string $resource
 * @property string $value
 */
class Item implements \ArrayAccess
{
    use SmartObject;

	/** @var string */
	private $name;

	/** @var string */
	private $label;

	/** @var string */
	private $link;

	/** @var mixed */
	private $linkArgs = [];

	/** @var string */
	private $icon;

	/** @var string */
	private $resource;

	/** @var string */
	private $value;

	/** @var boolean */
	private $active = TRUE;

	/** @var boolean */
	private $current = FALSE;

	/** @var array */
	private $items = [];

	/** @var array */
	private $options = [];


	/**
	 * @param string $label
	 * @param string $link
	 * @param string $icon
	 * @param string $resource
	 */
	public function __construct($label, $link, $icon = NULL, $resource = NULL)
	{
		$this->label = $label;
		$this->icon = $icon;
		$this->resource = $resource;
		$this->link = $link ? $link : '#';
	}


	/**
	 * @param string $name
	 * @param string $label
	 * @param string $link
	 * @param string $icon
	 * @param string $resource
	 * @return Item
	 */
	public function addItem($name, $label, $link, $icon = NULL, $resource = NULL)
	{
		$item = new Item($label, $link, $icon, $resource);
		return $this[$name] = $item;
	}


	/**
	 * @param bool $deep
	 * @return Item[]
	 */
	public function getItems($deep = FALSE)
	{
		$items = array_values($this->items);
		if ($deep) {
			foreach($this->items as $item) {
				$items = array_merge($items, $item->getItems(TRUE));
			}
		}
		return $items;
	}


	/**
	 * @param string $name
	 * @return Item
	 */
	public function getItem($name)
	{
		if (!isset($this->items[$name])) {
			throw new InvalidArgumentException("Item with name '$name' does not exist.");
		}
		return $this->items[$name];
	}


	/**
	 * @return Item|null
	 */
	public function getCurrentItem()
	{
		if ($this->isCurrent()) {
			return $this;
		}
		foreach($this->getItems(TRUE) as $item) {
			if ($item->isCurrent()) {
				return $item;
			}
		}
		return NULL;
	}


	public function isOpen()
	{
		if ($this->isCurrent()) {
			return TRUE;
		}
		foreach ($this->getItems() as $item) {
			if ($item->isCurrent() or $item->isOpen()) {
				return TRUE;
			}
		}
		return FALSE;
	}


	public function isDropdown()
	{
		foreach ($this->getItems() as $item) {
			if ($item->isActive()) {
				return TRUE;
			}
		}
		return FALSE;
	}


	public function isUrl()
	{
		return (Validators::isUrl($this->link) or preg_match('~^/[^/]~', $this->link) or $this->link[0] == '#');
	}


	/**
	 * @return Items[]
	 */
	public function getPath()
	{
		$items = [];
		foreach ($this->getItems(TRUE) as $item) {
			if ($item->isCurrent() || $item->isOpen()) {
				$items[$item->link] = $item;
			}
		}
		if ($items) {
			$items = [$this->link => $this] + $items;
		}
		return $items;
	}


	public function getValue()
	{
		return is_callable($this->value) ? call_user_func_array($this->value, [$this]) : $this->value;
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return self
	 */
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;
		return $this;
	}


	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getOption($name, $default = NULL)
	{
		return isset($this->options[$name]) ? $this->options[$name] : $default;
	}


	public function setName($name)
	{
		if (!preg_match('~^[a-zA-Z0-9_]+~', $name)) {
			throw new InvalidArgumentException("Name must be non-empty alphanumeric string, '$name' given.");
		}
		$this->name = $name;
		return $this;
	}


	public function __toString()
	{
		return (string)$this->label;
	}


	public function offsetExists($offset)
	{
		return isset($this->items[$offset]);
	}


	public function offsetGet($name)
	{
		$item = $this;
		foreach (explode('-', $name) as $key) {
			$item = $item->getItem($key);
		}
		return $item;
	}


	public function offsetSet($name, $item)
	{
		if (!$item instanceof Item) {
			throw new InvalidArgumentException(sprintf('Value must be %s, %s given.', get_called_class(), gettype($item)));
		}
		$item->setName($name);
		$this->items[$name] = $item;
	}


	public function offsetUnset($offset)
	{
		unset($this->items[$offset]);
	}

    public function setLinkArgs( $linkArgs)
    {
        $this->linkArgs = $linkArgs;
    }
    public function setActive( $active)
    {
        $this->active = $active;
    }
    public function setCurrent( $current)
    {
        $this->current = $current;
    }
    public function setValue( $value)
    {
        $this->value = $value;
    }
    public function setIcon( $icon)
    {
        $this->icon = $icon;
    }
    public function setOptions( $options)
    {
        $this->options = $options;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getLabel()
    {
        return $this->label;
    }
    public function getLink()
    {
        return $this->link;
    }
    public function getLinkArgs()
    {
        return $this->linkArgs;
    }
    public function getIcon()
    {
        return $this->icon;
    }
    public function getResource()
    {
        return $this->resource;
    }
    public function isActive()
    {
        return $this->active;
    }
    public function isCurrent()
    {
        return $this->current;
    }

}
