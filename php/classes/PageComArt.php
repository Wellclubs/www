<?php

/**
 * Description of PageComArt
 */
abstract class PageComArt
{
 private static $arts = array();

 private static function createArt($id)
 {
  $class = __CLASS__ . ucfirst($id);
  self::$arts[$id] = new $class($id);
 }

 public static function &arts()
 {
  if (!count(self::$arts))
  {
   foreach (array('home', 'brief', 'msg', 'cdr', /*'pmts', 'clts',*/ 'clt', 'srvs', 'sgr', 'srv', 'mtrs', 'mtr'/*, 'ctrs', 'bnds'*/, 'bnd') as $id)
    self::createArt($id);
  }
  return self::$arts;
 }

 public static function &art($id)
 {
  $arts = &self::arts();
  if (!array_key_exists($id, $arts))
   return $arts;
  return $arts[$id];
 }

 private $id;
 public function id() { return $this->id; }

 public function __construct($id)
 {
  $this->id = $id;
 }

 /**
  * Common article initialization (must be as quick as possible)
  */
 public function init()
 {
 }

 /**
  * Full article initialization (is being called for active article only)
  */
 public function initData()
 {
 }

 protected $title = '';
 protected $useParIndex = false;
 public function useParIndex() { return $this->useParIndex; }

 protected $useForCtr = false; ///< Show an article in 'ctr' mode only
 public function useForCtr() { return $this->useForCtr; }

 // http://www.christchurch-harbour-hotel.co.uk/topic/events/
 // There are currently no items in this category, please check back soon.

 protected $tabs = array();
 public function &tabs() { return $this->tabs; }
 public function usesTabs() { return !!count($this->tabs); }

 const TABS_UPPER_SIDE = 0;
 const TABS_LOWER_SIDE = 1;
 const TABS_UPPER_LINE = 2;
 protected $tabPosition = self::TABS_UPPER_SIDE;

 public function active()
 {
  return Base::par() === $this->id();
 }

 /**
  * Test the article for common show-as-active permission
  * @return bool True if the article can be shown
  */
 public function canBeShown()
 {
  if ($this->useForCtr && (Base::mode() != 'ctr'))
   return false;
  if ($this->useParIndex && (!Base::parIndex()))
   return false;
  return $this->testPrivs();
 }

 /**
  * Test the article for special client privileges (can be overridden)
  * @return bool True if the article can be shown to the current client
  */
 public function testPrivs()
 {
  return false;
 }

 /**
  * Test the article for special client privileges (can be overridden)
  * @return bool True if the article can be edited by the current client
  */
 public function canBeEdited()
 {
  return false;
 }

 /**
  * Can the article be shown in menu (sometimes)
  * @return bool True if the article can be shown in menu
  */
 public function useInMenu()
 {
  if (!strlen($this->title))
   return false;
  if ($this->useParIndex)
   return false;
  return true;
 }

 /**
  * Does the article have to be shown in menu (just now)
  * @return bool True if the article has to be shown in menu
  */
 public function showInMenu()
 {
  if (!$this->useInMenu())
   return false;
  if ($this->useForCtr && !PageCom::ctr()/*(Base::mode() != 'ctr')*/)
   return false;
  if ($this->useForCtr && !$this->testPrivs())
   return false;
  return true;
 }

 public function getMenuHref()
 {
  $href = '';
  //if ((Base::mode() == 'ctr') && $this->useForCtr)
  // $href .= 'ctr-' . Base::index() . '/';
  if ($this->useForCtr && (PageCom::ctrId() !== false))
   $href .= 'ctr-' . PageCom::ctrId() . '/';
  if ($this->id !== ($this->useForCtr ? 'brief' : 'home'))
   $href .= $this->id . '/';
  return $href;
 }

 public function putMenuItem()
 {
  if (!$this->useInMenu())
   return;
  $class = 'ax';
  if ($this->useForCtr)
   $class .= ' ctr';
  if ($this->active())
   $class .= ' active';
  $hide = $this->canBeShown() ? '' : ' style="display:none;"';
  echo '<td><a href="' . $this->getMenuHref() . '" id="menu-' . $this->id . '" class="' . $class . '"' . $hide . '>';
  echo '<div class="marker"></div><div class="text">';
  $title = $this->title;
  if ($this->id == 'brief')
  {
   $ctr = PageCom::ctr();
   if ($ctr)
   {
    $title = $ctr['name'];
    if (mb_strlen($title) > 20)
     $title = trim(mb_substr($title, 0, 20)) . '...';
   }
  }
  echo htmlspecialchars(Lang::getPageWord('menu', $title)) . '</div></a></td>' . "\n";
 }

 public function putArticle()
 {
  $this->putArtBegin();
  $this->putArtBody();
  $this->putArtEnd();
 }

 private function putArtBegin()
 {
  $id = $this->id();
  $display = '';
  if ($id !== Base::par())
   $display = ' style="display:none"';
  echo '<article id="art-' . $id . '"' . $display . '>' . "\n";
  if (!count($this->tabs))
  {
   $this->putSide();
   echo '<div class="main">' . "\n";
  }
 }

 private function putArtEnd()
 {
  if (!count($this->tabs))
   echo '</div>' . "\n";
  echo '</article>' . "\n\n";
 }

 protected function putTabsBegin()
 {
  if (!count($this->tabs))
   return;
  $path = $this->getTabPath();
  echo '<table class="tabholder"><tr><td class="tags ui-state-default">' . "\n"; // ui-tabs-nav ui-widget-header(?)
  if ($this->tabPosition == self::TABS_UPPER_SIDE)
   $this->putTags($path);
  $this->putSide();
  if ($this->tabPosition == self::TABS_LOWER_SIDE)
   $this->putTags($path);
  echo '</td><td class="work">' . "\n";
  echo '<div class="line ui-state-default">' . "\n";
  //echo '<div class="filter">' . "\n";
  //echo '<select></select>' . "\n";
  //echo '</div>' . "\n"; // filter
  echo '<div class="taps">' . "\n";
  if ($this->tabPosition == self::TABS_UPPER_LINE)
   $this->putTags($path);
  echo '</div>' . "\n"; // taps
  echo '</div>' . "\n"; // line
  echo '<div class="tabs">' . "\n";
 }

 protected function putTags($path)
 {
  foreach ($this->tabs as $id => $title)
  {
   $active = ($id == Base::tab()) ? ' active js ui-state-active' : '';
   echo '<a href="' . $this->getTabHref($path, $id) . '" class="ax tab ui-state-default tab-' . $id . $active . '">';
   echo '<div class="label">' . htmlspecialchars(self::getLangTabTitle($title)) . '</div>';
   echo '</a>' . "\n";
  }
 }

 protected function putTabsEnd()
 {
  if (!count($this->tabs))
   return;
  echo '</div></td></tr></table>' . "\n";
 }

 protected function putTabBegin($id)
 {
  $display = ($id == Base::tab()) ? '' : ' style="display:none"';
  echo '<div class="tab tab-' . $id . '"' . $display . '>' . "\n";
 }

 protected function putTabEnd()
 {
  echo '</div>' . "\n";
 }

 protected function putSide()
 {
  $method = 'putSideBody';
  if (!method_exists($this, $method))
   return;
  echo '<div class="side">' . "\n";
  $this->$method();
  echo '</div>' . "\n";
 }

 protected function putPaneBegin($class)
 {
  echo '<div class="pane ' . $class . '">' . "\n";
 }

 protected function putPaneEnd()
 {
  echo '</div>' . "\n";
 }

 /**
  * Put a begin part of a container for blocks
  * @param string $class Container CSS class name
  * @param string $object Object title name
  * @param string $noDataName No-data title name
  */
 protected static function putBlocksBegin($class = null, $object = null, $noDataName = null)
 {
  if (strlen($noDataName))
   echo '<div class="block no-data">' . htmlspecialchars(Lang::getPageWord('no-data', $noDataName)) . '</div>' . "\n";
  $attrs = ' class="blocks ' . $class . '"';
  if (strlen($object))
   $attrs .= ' object="' . Lang::getPageWord('object', $object) . '"';
  echo '<div' . $attrs . '>' . "\n";
 }

 /**
  * Put an and part of a container for blocks
  */
 protected static function putBlocksEnd()
 {
  echo '</div>' . "\n";
 }

 /**
  * Put a container for blocks
  * @param string $class Container CSS class name
  * @param string $object Object title name
  * @param string $noDataName No-data title name
  */
 protected static function putBlocks($class = null, $object = null, $noDataName = null)
 {
  self::putBlocksBegin($class, $object, $noDataName);
  self::putBlocksEnd();
 }

 private static function putTableAttr(&$attrs, array &$col, $key)
 {
  if (array_key_exists($key, $col))
   $attrs .= ' ' . $key . '="' . $col[$key] . '"';
 }

 /**
  *
  * @param array $cols
  * @param string $noDataName No-data text key
  * @param bool $block Use class name "block"
  * @param bool $small Use class name "small"
  * @param string $noDataText Ready no-data text
  * @param string $class CSS class for table
  */
 protected static function putTable(array $cols, $noDataName = null, $block = true, $small = false, $noDataText = null, $class = null)
 {
  if (strlen($noDataName))
   $noDataText = Lang::getPageWord('no-data', $noDataName) . (strlen($noDataText) ? $noDataText : '');
  if (strlen($noDataText))
   echo '<div class="' . ($block ? 'block ' : '') . ($small ? 'small ' : '') . 'no-data" style="display:none">' .
     $noDataText . '</div>' . "\n";
  if ($block)
   echo '<div class="block" style="display:none">';
  $class = 'list' . ($small ? ' small' : '') . ($class ? (' ' . $class) : '');
  echo '<table class="' . $class . '" style="display:none"><tbody>' . "\n";
  echo '<tr' . ($small ? ' style="display:none"' : '') . '>' . "\n";
  foreach ($cols as $col)
  {
   $attrs = ' class="prompt' . (array_key_exists('class', $col) ? (' ' . $col['class']) : '') . '"';
   self::putTableAttr($attrs, $col, 'width');
   self::putTableAttr($attrs, $col, 'field');
   self::putTableAttr($attrs, $col, 'uri');
   self::putTableAttr($attrs, $col, 'databtn-style');
   self::putTableAttr($attrs, $col, 'databtn-text');
   self::putTableAttr($attrs, $col, 'bg');
   self::putTableAttr($attrs, $col, 'action');
   if (array_key_exists('object', $col))
    $attrs .= ' object="' . Lang::getPageWord('object', $col['object']) . '"';
   $text = array_key_exists('text', $col) ? htmlspecialchars(Lang::getPageWord('prompt', $col['text'])) :
     (array_key_exists('title', $col) ? htmlspecialchars($col['title']) : '');
   echo '<th' . $attrs . '>' . $text . '</th>' . "\n";
  }
  echo '</tr>' . "\n";
  echo '</tbody></table>' . ($block ? '</div>' : '') . "\n";
 }

 /**
  *
  * @param array $rows
  * @param bool $block Use class name "block"
  */
 protected static function putForm(array $rows, $block = true)
 {
  echo ($block ? '<div class="block">' : '') . '<table class="form">' . "\n";
  echo '<colgroup><col width="200"><col></colgroup>' . "\n";
  foreach ($rows as $row)
  {
   $rowclass = array_key_exists('rowclass', $row) ? (' class="' . $row['rowclass'] . '"') : '';
   if (array_key_exists('field', $row) && array_key_exists('name', $row))
   {
    $field = $row['field'];
    $class = 'data';
    if (array_key_exists('class', $row))
     $class .= ' ' . $row['class'];
    // Name (prompt)
    $name = $row['name'];
    $prompt = htmlspecialchars(array_key_exists('abc', $row) ? self::makeLangPrompt($name) : Lang::getSiteWord('prompt', $name));
    // Text (editing value)
    $text = "<a></a>";
    // Button (editing)
    if (array_key_exists('edit', $row))
    {
     $class .= ' edit';
     $text = '<div class="btn"></div>' . $text;
    }
    // Cell html text
    echo "<tr$rowclass><th field=\"$field\" class=\"prompt\">$prompt</th><td field=\"$field\" class=\"$class\">$text</td></tr>\n";
   }
   else if (array_key_exists('button', $row))
   {
    $class = 'button';
    if (array_key_exists('class', $row))
     $class .= ' ' . $row['class'];
    echo "<tr$rowclass><th colspan=\"2\"><div class=\"$class\">" . Lang::getPageWord('button', $row['button']) . "</div></th></tr>\n";
   }
  }
  echo '</table>' . ($block ? '</div>' : '') . "\n";
 }

 protected static function putLogo()
 {
  echo '<div class="block image">' . "\n";
  echo '<div class="title">' . Lang::getPageWord('title', 'Logo') . '</div><table>' . "\n";
  echo '<tbody><tr><td class="thumb">' . "\n";
  echo '<img width="300"/>' . "\n";
  echo '</td><td>' . "\n";
  $rows = array();
  $rows[] = array('button' => 'Upload', 'class' => 'left upload', 'rowclass' => 'edit');
  $rows[] = array('field' => 'file', 'name' => 'File name', 'class' => 'right', 'rowclass' => 'hide');
  $rows[] = array('field' => 'size', 'name' => 'File size', 'class' => 'right', 'rowclass' => 'hide');
  $rows[] = array('field' => 'rect', 'name' => 'Image size', 'class' => 'right', 'rowclass' => 'hide');
  $rows[] = array('button' => 'Clear', 'class' => 'right clear', 'rowclass' => 'edit hide');
  self::putForm($rows, false);
  echo '</td></tr></tbody></table></div>' . "\n";
 }

 protected static function putImage($id)
 {
  echo '<div class="block image" rowid="' . $id . '"><table><tbody><tr><td class="thumb">' . "\n";
  echo '<img height="200"/>' . "\n";
  echo '</td><td>' . "\n";
  $rows = array();
  $rows[] = array('button' => 'Upload', 'class' => 'left upload', 'rowclass' => 'edit');
  $rows[] = array('field' => 'file', 'name' => 'File name', 'class' => 'right', 'rowclass' => 'hide');
  $rows[] = array('field' => 'size', 'name' => 'File size', 'class' => 'right', 'rowclass' => 'hide');
  $rows[] = array('field' => 'rect', 'name' => 'Image size', 'class' => 'right', 'rowclass' => 'hide');
  $rows[] = array('field' => 'titles', 'name' => 'Title', 'class' => 'abc', 'rowclass' => 'hide', 'edit' => 1);
  $rows[] = array('button' => 'Clear', 'class' => 'right clear', 'rowclass' => 'edit hide');
  self::putForm($rows, false);
  echo '</td></tr></tbody></table></div>' . "\n";
 }

 /**
  * Put an action bar with a single action button
  * @param string $action Button action name
  * @param string $title Button caption
  * @param bool $left Align a button left (or right) inside the bar
  * @param bool $edit Hide this bar for read-only (non-editable) views
  * @param bool $block Use class name "block"
  */
 protected static function putSingleAction($action, $title, $left, $edit, $block = true)
 {
  $align = $left ? 'left' : 'right';
  $class = ($block ? 'block ' : '') . 'action' . ($edit ? ' edit' : '');
  echo '<div class="' . $class . '"><div class="button ' . $align . ' ' . $action . '" action="' . $action . '">';
  echo htmlspecialchars(Lang::getPageWord('button', $title));
  echo '</div></div>' . "\n";
 }

 /**
  * Put an action bar with a set of action buttons
  * @param array $buttons set of buttons: array(array('class' => '', 'title' => '', 'right' => ''))
  * @param bool $edit Hide this bar for read-only (non-editable) views
  * @param bool $block Use class name "block"
  */
 protected static function putMultiActions(array $buttons, $edit, $block = true)
 {
  $class = ($block ? 'block ' : '') . 'action' . ($edit ? ' edit' : '');
  echo '<div class="' . $class . '">';
  foreach ($buttons as $button)
  {
   $align = array_key_exists('right', $button) ? 'right' : 'left';
   $action = array_key_exists('action', $button) ? $button['action'] : '';
   $class = 'button ' . $align . (array_key_exists('class', $button) ? (' ' . $button['class']) : '');
   echo '<div class="' . $class . '" action="' . $action . '">';
   echo htmlspecialchars(Lang::getPageWord('button', $button['title']));
   echo '</div>' . "\n";
  }
  echo '</div>' . "\n";
 }

 /*public static function putBoxes()
 {
  echo '<div class="grps">';
  echo '</div>' . "\n";
 }*/

 protected static function putMenuItems(array $items, $prompt = null)
 {
  echo '<div class="menu">';
  if ($prompt)
   echo '<div class="prompt">' . htmlspecialchars(Lang::getPageWord('prompt', $prompt)) . "</div>\n";
  foreach ($items as $item)
  {
   $class = ' class="item button left' . (array_key_exists('class', $item) ? (' ' . $item['class']) : '') . '"';
   $href = ' href="' . (array_key_exists('href', $item) ? ($item['href']) : '#') . '"';
   $attrs = '';
   if (array_key_exists('attrs', $item))
    foreach ($item['attrs'] as $name => $value)
     $attrs .= ' ' . $name . '="' . $value . '"';
   echo "<a$class$href$attrs>" . $item['html'] . "</a>\n";
  }
  echo '</div>' . "\n";
 }

 protected static function putTextArea()
 {
  echo "<div class=\"block text frame\">\n";
  $menu = array();
  $views = '';
  foreach (Lang::map() as $id => $lang)
  {
   $class = (($id == Lang::current()->id()) ? ' def active' : '');
   $attrs = array('lang' => $id);
   $html = htmlspecialchars($lang->title());
   $menu[] = array('class' => 'js' . $class, 'attrs' => $attrs, 'html' => $html);
   $views .= "<div class=\"text html$class\" lang=\"$id\" style=\"display:none\"></div>\n";
  }
  self::putMenuItems($menu, 'Select a language');
  echo "<div class=\"viewer\">\n";
  $button1 = array('class' => 'add', 'title' => 'Add text');
  $button2 = array('class' => 'edit', 'title' => 'Edit text');
  self::putMultiActions(array($button1, $button2), true, false);
  echo $views;
  echo "</div>\n";
  echo "<div class=\"editor\" style=\"display:none\">\n";
  self::putSingleAction('cancel', 'Cancel editing', true, false, false);
  echo "<textarea class=\"text auto-focus\"></textarea>\n";
  self::putSingleAction('save', 'Save', false, false, false);
  echo "</div>\n";
  echo "</div>\n";
 }

 protected static function makeLangPrompt($prompt)
 {
  return Lang::getPageWord('prompt', 'Language') . ' "' . $prompt . '"';
 }

 protected function putArtBody()
 {
  if (count($this->tabs))
  {
   self::putTabsBegin();
   foreach ($this->tabs as $id => $tab)
   {
    self::putTabBegin($id);
    $method = 'putTabBody' . ucfirst($id);
    if (method_exists($this, $method))
     $this->$method();
    else
     echo '<div class="const">' . Base::htmlConst() . '</div>';
    self::putTabEnd();
   }
   self::putTabsEnd();
  }
  else
   echo Base::htmlConst();
 }

 public function getArtTitle($withMode)
 {
  $result = $withMode ? self::getModeTitle() : '';
  $parTitle = $this->getParTitle();
  if (strlen($parTitle))
   $result .= (strlen($result) ? ': ' : '') . $parTitle;
  $tabTitle = (count($this->tabs)) ? $this->getTabTitle(Base::tab()) : '';
  if (strlen($tabTitle))
   $result .= (strlen($result) ? ': ' : '') . $tabTitle;
  return $result;
 }

 public static function getModeTitle()
 {
  switch (Base::mode())
  {
  case 'ctr' :
   return Lang::getObjTitle('Centre') . ' "' . WCentre::getTitle(Base::index()) . '"';
  }
  return '';
 }

 protected function getParTitle()
 {
  if ($this->id === Base::getPage()->getDefaultPar())
   return '';
  return Lang::getPageWord('menu', $this->title);
 }

 protected function getTabTitle($tab)
 {
  return self::getLangTabTitle($this->tabs[$tab]);
 }

 protected static function getLangTabTitle($title)
 {
  return Lang::getPageWord('tab', $title);
 }

 /**
  * Get the base (common) part for all tabs of this article
  * @return string Base part for all tabs of this article
  */
 private function getTabPath()
 {
  if ($this->useForCtr && (Base::mode() !== 'ctr'))
   return '';
  if ($this->useParIndex && !$this->active())
   return '';
  // Add a mode URI part for some articles (if mode is explicitly set)
  $path = ($this->useForCtr || $this->useParIndex) ? Base::pathMode() : '';
  // Add an article URI part (for non-default articles)
  if ($this->id !== ($this->useForCtr ? 'brief' : 'home'))
   $path .= ($this->id . ($this->useParIndex ? ('-' . Base::parIndex()) : '') . '/');
  // Result path is ready
  return $path;
 }

 /**
  * Add to the URI path a tab-specific suffix part
  * @param string $path Base part for all tabs of this article
  * @param string $id Id of the tab to make a specific href
  * @return string Correct tab-specific href
  */
 protected function getTabHref($path, $id)
 {
  if ($this->useForCtr && (Base::mode() !== 'ctr'))
   return '';
  if ($this->useParIndex && !$this->active())
   return '';
  return $path . ($id === Base::getPage()->getDefaultTab() ? '' : ($id . '/'));
 }

 public function ajax()
 {
  if (count($this->tabs))
  {
   if ($this->useForCtr || $this->useParIndex)
   {
    $tabs = array();
    $path = $this->getTabPath();
    foreach ($this->tabs as $id => $name)
     $tabs[$id] = array('href' => $this->getTabHref($path, $id));
    PageCom::addToAjax('tabs', $tabs);
   }
   $method = 'ajaxTab' . ucfirst(Base::tab());
   if (method_exists($this, $method))
    $this->$method();
  }
 }

 public static function actionError($text)
 {
  PageCom::addToAjax('error', Lang::getPageWord('error', $text));
  return false;
 }

 public static function actionErrorAccess()
 {
  PageCom::addToAjax('error', Lang::getPageWord('error', 'Access denied'));
  return false;
 }

 public static function actionFail($text)
 {
  PageCom::addToAjax('failure', $text);
  return false;
 }

 public static function actionFailDB($text)
 {
  PageCom::addToAjax('failure', $text . ': ' . DB::lastQuery());
  return false;
 }

 public static function actionFailDBInsert()
 {
  self::actionFailDB('Error inserting new record to the database');
  return false;
 }

 public static function actionFailDBUpdate()
 {
  self::actionFailDB('Error updating a record in the database');
  return false;
 }

 public static function actionFailDBDelete()
 {
  self::actionFailDB('Error deleting a record from the database');
  return false;
 }

 public function processAnyAction($action)
 {
  $Action = ucfirst($action);
  $method = 'processAction';
  if (count($this->tabs))
  {
   $methodTab = $method . 'Tab' . ucfirst(Base::tab());
   $methodAction = $methodTab . $Action;
   if (method_exists($this, $methodAction))
    return $this->$methodAction();
   if (method_exists($this, $methodTab))
    return $this->$methodTab($action);
  }
  $methodAction = $method . $Action;
  if (method_exists($this, $methodAction))
   return $this->$methodAction();
  if (method_exists($this, $method))
   return $this->$method($action);
  return false;
 }

 public function processAnyAcQuery($kind, $term)
 {
  $Kind = ucfirst($kind);
  $method = 'processAcQuery';
  if (count($this->tabs))
  {
   $methodTab = $method . 'Tab' . ucfirst(Base::tab());
   $methodKind = $methodTab . $Kind;
   if (method_exists($this, $methodKind))
    return $this->$methodKind($term);
   if (method_exists($this, $methodTab))
    return $this->$methodTab($kind, $term);
  }
  $methodKind = $method . $Kind;
  if (method_exists($this, $methodKind))
   return $this->$methodKind($term);
  if (method_exists($this, $method))
   return $this->$method($kind, $term);
  return false;
 }

 /**
  * Process the result of an action executing function
  * If success then count($result) == 1 and $result[0] is the result
  * Else count($result) == 2 and $result[0] is the error type, $result[1] is the message
  * @param array $result array of 1 or 2 values
  * @return mixed $result[0] if success, else false
  */
 protected static function processResult($result)
 {
  if (!is_array($result))
   return self::actionFail('Invalid result type (not an array)');
  if (count($result) == 1)
   return $result[0];
  if (count($result) != 2)
   return self::actionFail('Invalid result (count == ' . count($result) . ')');
  switch ($result[0])
  {
   case 'error':
    return self::actionError($result[1]);
   case 'fail':
    return self::actionFail($result[1]);
   case 'faildb':
    return self::actionFailDB($result[1]);
  }
  return self::actionFail('Invalid result code: "' . $result[0] . '"');
 }

 /**
  * Process action 'send' (Send a message from client)
  * @return bool
  */
 protected function processActionSend()
 {
  $msgid = self::processResult(WMessage::actionSendFromClient());
  if ($msgid === false)
   return false;
  PageCom::addToAjax('msgid', $msgid, true);
  return true;
 }

 protected function createGallery()
 {
  throw new Exception('Gallery is not implemented for class ' . __CLASS__);
 }

 protected function putTabBodyImgs()
 {
  for ($i = 1; $i <= 5; $i++)
   self::putImage($i);
 }

 protected function ajaxTabImgs()
 {
  $this->createGallery()->getImagesCom();
 }

 protected function processActionTabImgsUpload()
 {
  $rowid = HTTP::param('rowid');
  $gallery = $this->createGallery();
  if (!$gallery->uploadImage($rowid))
   return self::actionFailDB('Error uploading an image');
  PageCom::addToAjaxData('img', $gallery->getImageCom($rowid));
  return true;
 }

 protected function processActionTabImgsModify()
 {
  return $this->createGallery()->modifyImageCom();
 }

 protected function processActionTabImgsClear()
 {
  return $this->createGallery()->deleteImageCom();
 }
}

?>
