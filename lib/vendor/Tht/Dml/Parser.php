<?php

class Tht_Dml_Parser
{
    /**
     * creates hierarchical array which represents the
     * dbpedia mapping template from a given template
     *
     * @param string $markup
     * @param string $grammarFile path to the grammar file
     * @return array
     */
    public static function parse($markup, $grammarFile = null)
    {
        if($grammarFile === null){
            $grammarFile = __ROOT__ . '/grammar/dbpedia_mapping_grammar.xml';
        }
    
        // load grammar from xml file
        Tht_Dml_Grammar::loadGrammarFile($grammarFile);

        // split mapping language in tokens
        Tht_Dml_Tokenizer::parseTokensFromMarkup($markup);

        // initialize root to collect
        // comments and template
        $out = array();

        // fetch elements by iterating
        // template tokens
        while(!Tht_Dml_Tokenizer::isEmpty()){

            // add particular template
            $out[] = self::createTemplate();
            // $out = array_merge($out, self::createTemplate());
        }
        return $out;
    }

    /**
     * creates a template respecting comments
     * from the current token of internal TokenList
     *
     * @return array
     */
    public static function createTemplate()
    {
        // initialize root to collect
        // comments and template
        $out = array();

        // iterate over tokens
        while(!Tht_Dml_Tokenizer::isEmpty()){
            $chunk = Tht_Dml_Tokenizer::walk();

            // fetch comments:
            // {{ <comments> templatename
            if($chunk === DBPEDIA_TOKEN_COMMENT_START){
                $out[] = new Tht_Dml_Comment();
                continue;
            }

            // fetch template
            if($chunk === DBPEDIA_TOKEN_TEMPLATE_START || Tht_Dml_Tokenizer::show(-2) === DBPEDIA_TOKEN_TEMPLATE_START){

                // if token indicates template start
                // fetch next token
                if($chunk === DBPEDIA_TOKEN_TEMPLATE_START){
                    $chunk = Tht_Dml_Tokenizer::walk();
                }

                // if next token indicates a comment fetch
                // comment:
                // | property = {{ <comment> template
                if($chunk === DBPEDIA_TOKEN_COMMENT_START){
                    $out[] = new Tht_Dml_Comment();
                    
                    // after fetching the comment, the comment
                    // is stripped from the tokenlist, therefore
                    // go one step backwards and redo the tokens
                    // not containing the comment
                    Tht_Dml_Tokenizer::back();
                    continue;
                }

                // otherwise create template by fetching
                // the template master from the grammar
                $template = Tht_Dml_Grammar::getTemplateByName($chunk);
                $tmp      = $template->parse();
                
                // for comments inside templates
                // handle the comments like siblings of
                // the template, this will result in better
                // readable mappings
                //
                // {{<!-- comment1 -->Template<!-- comment2 -->|property = ..
                // will result in
                // <!-- comment1 -->
                // <!-- comment2 -->
                // {{Template|property = ..}}
                $out[] = $tmp['template'];
                if(isset($tmp['comments'])){
                    $out = array_merge($out, $tmp['comments']);
                }

                // return this particular array of templates
                return $out;
            }

            // token indicates neither comment nor template
            throw new Exception('unknown nested element ' . $chunk);
        }

        // return $out (array of templates) when TokenList:isEmpty() === true
        return $out;
    }
}