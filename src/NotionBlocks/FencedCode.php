<?php

namespace RoelMR\MarkdownToNotionBlocks\NotionBlocks;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode as CommonMarkFencedCode;
use RoelMR\MarkdownToNotionBlocks\Objects\NotionBlock;

class FencedCode extends NotionBlock {
    /**
     * Code constructor.
     *
     * @since 1.0.0
     *
     * @param CommonMarkFencedCode $node The code node.
     */
    public function __construct(public CommonMarkFencedCode $node) {}

    /**
     * @inheritDoc
     */
    public function object(): array {
        return array(
            'object' => 'block',
            'type' => 'code',
            'code' => array(
                'caption' => [],
                'rich_text' => $this->richText($this->node),
                'language' => $this->language(),
            ),
        );
    }

    /**
     * Get the language of the code block.
     *
     * Check the available languages in the Notion API documentation.
     *
     * @since 1.0.0
     *
     * @see https://developers.notion.com/reference/block#code
     *
     * @return string The language of the code block.
     */
    protected function language(): string {
        $default = 'plain text';
        $language = $this->node->getInfo() ?: $default;

        foreach ($this->notionLanguages() as $notionLanguage => $markdownLanguages) {
            if (in_array($language, $markdownLanguages)) {
                return $notionLanguage;
            }
        }

        return $default;
    }

    /**
     * Get the available languages in the Notion API.
     *
     * The key is the language in the Notion API and the value is an array of possible languages in markdown.
     *
     * @since 1.0.0
     *
     * @return array[] The available languages in the Notion API.
     */
    protected function notionLanguages(): array {
        return [
            'abap'          => ['abap'],
            'arduino'       => ['arduino'],
            'bash'          => ['bash', 'sh', 'shell'],
            'basic'         => ['basic'],
            'c'             => ['c'],
            'clojure'       => ['clojure'],
            'coffeescript'  => ['coffeescript', 'coffee'],
            'cpp'           => ['c++', 'cpp'],
            'csharp'        => ['c#', 'csharp'],
            'css'           => ['css'],
            'dart'          => ['dart'],
            'diff'          => ['diff'],
            'docker'        => ['docker'],
            'elixir'        => ['elixir'],
            'elm'           => ['elm'],
            'erlang'        => ['erlang'],
            'flow'          => ['flow'],
            'fortran'       => ['fortran'],
            'fsharp'        => ['f#', 'fsharp'],
            'gherkin'       => ['gherkin'],
            'glsl'          => ['glsl'],
            'go'            => ['go', 'golang'],
            'graphql'       => ['graphql'],
            'groovy'        => ['groovy'],
            'haskell'       => ['haskell'],
            'html'          => ['html'],
            'java'          => ['java'],
            'javascript'    => ['javascript', 'js'],
            'json'          => ['json'],
            'julia'         => ['julia'],
            'kotlin'        => ['kotlin'],
            'latex'         => ['latex'],
            'less'          => ['less'],
            'lisp'          => ['lisp'],
            'livescript'    => ['livescript'],
            'lua'           => ['lua'],
            'makefile'      => ['makefile'],
            'markdown'      => ['markdown', 'md'],
            'markup'        => ['markup'],
            'matlab'        => ['matlab'],
            'mermaid'       => ['mermaid'],
            'nix'           => ['nix'],
            'objective-c'   => ['objective-c', 'objc'],
            'ocaml'         => ['ocaml'],
            'pascal'        => ['pascal'],
            'perl'          => ['perl'],
            'php'           => ['php'],
            'plain text'    => ['plain text', 'plaintext', 'text'],
            'powershell'    => ['powershell', 'ps1'],
            'prolog'        => ['prolog'],
            'protobuf'      => ['protobuf'],
            'python'        => ['python', 'py'],
            'r'             => ['r'],
            'reason'        => ['reason'],
            'ruby'          => ['ruby', 'rb'],
            'rust'          => ['rust'],
            'sass'          => ['sass'],
            'scala'         => ['scala'],
            'scheme'        => ['scheme'],
            'scss'          => ['scss'],
            'shell'         => ['shell', 'bash'],
            'sql'           => ['sql'],
            'swift'         => ['swift'],
            'typescript'    => ['typescript', 'ts'],
            'vb.net'        => ['vb.net', 'vbnet'],
            'verilog'       => ['verilog'],
            'vhdl'          => ['vhdl'],
            'visual basic'  => ['visual basic', 'vb'],
            'webassembly'   => ['webassembly', 'wasm'],
            'xml'           => ['xml'],
            'yaml'          => ['yaml', 'yml'],
            'java/c/c++/c#' => ['java', 'c', 'c++', 'c#']
        ];
    }
}
