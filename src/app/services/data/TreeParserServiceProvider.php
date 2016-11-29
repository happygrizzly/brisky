<?php 

    namespace App\Services\Data\Misc;

    use Pimple\Container;
    use Pimple\ServiceProviderInterface;

    class TreeParserServiceProvider implements ServiceProviderInterface {        
        public function register(Container $app) {
            $app["services.tree_parser"] = new TreeParserService();
        }
    }

    class TreeParserService {
        
        /* 
            The entire thing could be parsed on the client side 
            (http://stackoverflow.com/questions/28432624/create-a-treeview-array-of-object-in-angular)
        
            function flatListToTreeViewData(dataList) {
                
                var tree = [],
                    mappedArr = {},
                    arrElem,
                    mappedElem;

                for (var i = 0, len = dataList.length; i < len; i++) {
                    arrElem = dataList[i];
                    mappedArr[arrElem.id] = arrElem;
                    mappedArr[arrElem.id]['children'] = [];
                }

                for (var id in mappedArr) {
                    if (mappedArr.hasOwnProperty(id)) {
                        mappedElem = mappedArr[id];
                        
                        if(mappedElem.parentID) {
                            mappedArr[mappedElem['parentID']]['children'].push(mappedElem);
                        } else {
                            tree.push(mappedElem);
                        }
                    }
                }

                return tree;
            }

            In python the same is achieved using the code:
            (http://stackoverflow.com/questions/16069840/rendering-a-tree-from-a-closure-table-select-statement)

            for record in comment_set:
                nodes[record['id']] = record
            for record in comment_set:
                if record['parent_id'] in nodes:
                    nodes[record['parent_id']]['children'].append(record)
                else
                    top = record;
            return top 

            task: build paths

        */

        // http://blog.tekerson.com/2009/03/03/converting-a-flat-array-with-parent-ids-to-a-nested-tree/

        public function parse(array $array, $idKeyName = 'id', $parentIdKey = 'id_parent', $childNodesField = 'children') {

            $indexed = array();

            // first pass - get the array indexed by the primary id

            foreach($array as $row) {
                $indexed[$row[$idKeyName]] = $row;
                $indexed[$row[$idKeyName]][$childNodesField] = array();
            }

            // second pass
            
            $root = array();

            foreach($indexed as $id => $row) {
                
                $indexed[$row[$parentIdKey]][$childNodesField][$id] = &$indexed[$id];
                
                if(!$row[$parentIdKey]) {
                    $root[$id] = &$indexed[$id];
                }
            }

            return $root;

        }

    }
    
?>