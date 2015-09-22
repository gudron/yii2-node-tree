<?php

namespace bariew\nodeTree;

class ARTreeMenuWidget extends \yii\base\Widget
{
    public $items;
    public $behavior; // ARTreeBehavior instance
    public $view = 'node';
    public $id = 'jstree';
    public $options = [];
    public $binds = [];
    public static $uniqueKey = 0;
    
    
    public function commonOptions()
    {
         return [
            "core" => [
                "check_callback" => true,
                "animation" => 0
            ],
            "plugins" => [
                "contextmenu", "dnd", "search", "types", "wholerow", 'state'
            ],
            "dnd"   => [
                "is_draggable"  => true,
                "copy"          => true
            ],
            "types" => [
                "file"        => ["icon" => "glyphicon glyphicon-file"],
                "book"          => ["icon" => "glyphicon glyphicon-book"],
            ],
            "search"  =>   [
                "fuzzy"   => false
            ],
            "themes" => [
                "theme" => "xenon",
                "dots" => false,
            ],
            "contextmenu" => [
                "show_at_node"    => false,
                "items" =>    [
                    "create"  =>  [
                        "label"   => "<i class='glyphicon glyphicon-plus' title='Create'></i>",
                        "action" => 'function(obj){
                            var url = replaceTreeUrl($(obj.reference[0]).attr("href"), "create");
                            window.location.href = url;
                        }'
                    ],
                    "rename"  => [
                        "label"  => "<i class='glyphicon glyphicon-font' title='Rename'></i>",
                        "action"  => 'function(obj){
                            var sel = jstree.jstree(true).get_selected();
                            arTreeRename(sel[0], false, jstree);
                        }'
                    ],
                    "edit"    => [
                        "label"   => "<i class='glyphicon glyphicon-pencil' title='Edit'></i>",
                        "action"  => 'function(obj){
                            var url = replaceTreeUrl( $(obj.reference[0]).attr("href"), "update");
                            arTreeShowUpdate(url);
                        }'
                    ],
                    "delete" => [
                        "label"   => "<i class='glyphicon glyphicon-trash' title='Delete'></i>",
                        "action" => 'function(obj) {
                            var url = replaceTreeUrl($(obj.reference[0]).attr("href"), "delete");
                            if(confirm("Delete node?")) {
                                $.post(url, {
                                    '.\Yii::$app->request->csrfParam.':"'.\Yii::$app->request->csrfToken.'"
                                }, function(){
                                    var ref = jstree.jstree(true),
                                    sel = ref.get_selected();
                                    if(!sel.length) { return false; }
                                    ref.delete_node(sel);
                                });
                            }
                        }'
                    ]
                ]
            ]
        ];
    }
    
    public function commonBinds()
    {
        return [
            'move_node.jstree'  => 'function(event, data){
                $.ajax({
                    type: "POST",
                    url: replaceTreeUrl(data.node.a_attr.href, "tree-move"),
                    data: {
                        pid     : data.parent.replace(/.*-id-(.*)$/, "$1"),
                        position: data.position
                    },
                    success: function(response){
                        var attributes = JSON.parse(response);
                        $("a[data-id="+data.node.id+"]").prop("href", attributes.a_attr.href);
                    },
                    error: function(xhr, status, error){
                        alert(status);
                    }
                });
            }',
                'copy_node.jstree'  => 'function(event, data){
                $.ajax({
                    type: "POST",
                    url: replaceTreeUrl(data.node.a_attr.href, "tree-copy"),
                    data: {
                        pid     : data.parent.replace(/.*-id-(.*)$/, "$1"),
                        position: data.position
                    },
                    success: function(response){
                        var attributes = JSON.parse(response);
                        $("a[data-id="+data.node.id+"]").prop("href", attributes.a_attr.href);
                    },
                    error: function(xhr, status, error){
                        alert(status);
                    }
                });
            }',
                'select_node.jstree'  => 'function(event, data){
                if (data.event.which != 1) {
                    return;
                }
                window.location.href = data.node.a_attr.href;
            }'
        ];
    }
    
    public function run()
    {
        if(!$items = $this->items){
            return;
        }
        $this->registerScripts();
        return $this->render($this->view, [
            'childView' => '_' . $this->view,
            'items'     => $this->items, 
            'behavior'  => $this->behavior
        ]);
    }
    
    public function registerScripts()
    {
        $view = $this->getView();
        ARTreeAssets::register($view);
        $options = $this->jsonEncode(array_merge($this->commonOptions(), $this->options));
        $binds = array_merge($this->commonBinds(), $this->binds);
        $content = "var jstree = $('#{$this->id}'); jstree.jstree({$options});";
	    foreach($binds as $event => $function){
            $content .= "jstree.bind('".$event."', $function);";
        }
        $view->registerJs($content);
    }
    
    public $jsonValues = [];
    public $jsonKeys = [];

    /**
     *
     * @param type $content
     * @param int $level
     * @return type
     * @link http://solutoire.com/2008/06/12/sending-javascript-functions-over-json/
     */
    public function jsonEncode($content, $level = 0)
    {
        foreach($content as &$value){
            if (is_array($value)) {
                $value = $this->jsonEncode($value, 1);
                continue;
            }
            if(strpos($value, 'function(')===0){
                $this->jsonValues[] = $value;
                $value = '%' . md5($value) . '%';
                $this->jsonKeys[] = '"' . $value . '"';
            }
        }
        return ($level > 0)
            ? $content
            : str_replace($this->jsonKeys, $this->jsonValues, json_encode($content));
    }

    public static function this()
    {
        return new self();
    }
}