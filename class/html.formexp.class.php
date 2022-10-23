<?php

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

class FormExp extends Form{
  public function showCategoriesExcluding($id, $type, $categoriesExlues, $rendermode = 0, $nolink = 0)
     {
         include_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

         $cat = new Categorie($this->db);
         $categories = $cat->containing($id, $type);

         if ($rendermode == 1) {
             $toprint = array();
             foreach ($categories as $c) {
               if( in_array(strval($c->id), $categoriesExlues)) continue;
                 $ways = $c->print_all_ways(' &gt;&gt; ', ($nolink ? 'none' : ''), 0, 1); // $ways[0] = "ccc2 >> ccc2a >> ccc2a1" with html formated text
                 foreach ($ways as $way) {
                     $toprint[] = '<li class="select2-search-choice-dolibarr noborderoncategories"'.($c->color ? ' style="background: #'.$c->color.';"' : ' style="background: #bbb"').'>'.$way.'</li>';
                 }
             }
             return '<div class="select2-container-multi-dolibarr"><ul class="select2-choices-dolibarr">'.implode(' ', $toprint).'</ul></div>';
         }

         if ($rendermode == 0) {
             $arrayselected = array();
             $cate_arbo = $this->select_all_categories($type, '', 'parent', 64, 0, 1);
             foreach ($categories as $c) {
               if( in_array(strval($c->id), $categoriesExlues)) continue;
                 $arrayselected[] = $c->id;
             }

             return $this->multiselectarray('categories', $cate_arbo, $arrayselected, '', 0, '', 0, '100%', 'disabled', 'category');
         }

         return 'ErrorBadValueForParameterRenderMode'; // Should not happened
     }

     public function showCategoriesListe($cats, $rendermode = 0, $nolink = 0){
       include_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

       $c = new Categorie($this->db);

       if ($rendermode == 1) {
           $toprint = array();
           foreach ($cats as $id) {

             $c->fetch($id);

             $ways = $c->print_all_ways(' &gt;&gt; ', ($nolink ? 'none' : ''), 0, 1); // $ways[0] = "ccc2 >> ccc2a >> ccc2a1" with html formated text
             foreach ($ways as $way) {
               $toprint[] = '<li class="select2-search-choice-dolibarr noborderoncategories"'.($c->color ? ' style="background: #'.$c->color.';"' : ' style="background: #bbb"').'>'.$way.'</li>';
             }
           }
           return '<div class="select2-container-multi-dolibarr"><ul class="select2-choices-dolibarr">'.implode(' ', $toprint).'</ul></div>';
       }

       if ($rendermode == 0) {
           $arrayselected = array();
           $cate_arbo = $this->select_all_categories($type, '', 'parent', 64, 0, 1);

           return $this->multiselectarray('categories', $cate_arbo, $cats, '', 0, '', 0, '100%', 'disabled', 'category');
       }

       return 'ErrorBadValueForParameterRenderMode'; // Should not happened
     }
}
